<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * Afficher la liste des messages reçus par l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Message::where('destinataire_id', $user->id);
        
        // Filtrage par lu/non lu
        if ($request->has('lu')) {
            $query->where('lu', $request->boolean('lu'));
        }
        
        // Filtrage par expéditeur
        if ($request->has('expediteur_id')) {
            $query->where('expediteur_id', $request->expediteur_id);
        }
        
        // Recherche par sujet ou contenu
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sujet', 'like', "%{$search}%")
                  ->orWhere('contenu', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $messages = $query->with('expediteur')->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $messages,
            'message' => 'Liste des messages reçus récupérée avec succès'
        ]);
    }

    /**
     * Afficher la liste des messages envoyés par l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function envoyes(Request $request)
    {
        $user = Auth::user();
        
        $query = Message::where('expediteur_id', $user->id);
        
        // Filtrage par destinataire
        if ($request->has('destinataire_id')) {
            $query->where('destinataire_id', $request->destinataire_id);
        }
        
        // Recherche par sujet ou contenu
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sujet', 'like', "%{$search}%")
                  ->orWhere('contenu', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $messages = $query->with('destinataire')->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $messages,
            'message' => 'Liste des messages envoyés récupérée avec succès'
        ]);
    }

    /**
     * Envoyer un nouveau message
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destinataire_id' => 'required|exists:utilisateurs,id',
            'sujet' => 'required|string|max:100',
            'contenu' => 'required|string|max:5000',
            'piece_jointe' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Vérifier si le destinataire existe et est actif
            $destinataire = Utilisateur::findOrFail($request->destinataire_id);
            if (!$destinataire->actif) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le destinataire n\'est pas actif'
                ], 400);
            }
            
            // Traiter la pièce jointe si elle existe
            $pieceJointePath = null;
            if ($request->hasFile('piece_jointe')) {
                $fichier = $request->file('piece_jointe');
                $extension = $fichier->getClientOriginalExtension();
                $nomFichier = Str::slug($request->sujet) . '-' . time() . '.' . $extension;
                
                $pieceJointePath = $fichier->storeAs(
                    'pieces_jointes/' . date('Y/m'),
                    $nomFichier,
                    'local'
                );
            }
            
            // Créer le message
            $message = Message::create([
                'expediteur_id' => $user->id,
                'destinataire_id' => $request->destinataire_id,
                'sujet' => $request->sujet,
                'contenu' => $request->contenu,
                'piece_jointe' => $pieceJointePath,
                'lu' => false,
            ]);
            
            // Charger les relations
            $message->load(['expediteur', 'destinataire']);
            
            return response()->json([
                'status' => 'success',
                'data' => $message,
                'message' => 'Message envoyé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi du message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un message à plusieurs destinataires
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function envoyerMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destinataires' => 'required|array',
            'destinataires.*' => 'required|exists:utilisateurs,id',
            'sujet' => 'required|string|max:100',
            'contenu' => 'required|string|max:5000',
            'piece_jointe' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $user = Auth::user();
            $messages = [];
            
            // Traiter la pièce jointe si elle existe
            $pieceJointePath = null;
            if ($request->hasFile('piece_jointe')) {
                $fichier = $request->file('piece_jointe');
                $extension = $fichier->getClientOriginalExtension();
                $nomFichier = Str::slug($request->sujet) . '-' . time() . '.' . $extension;
                
                $pieceJointePath = $fichier->storeAs(
                    'pieces_jointes/' . date('Y/m'),
                    $nomFichier,
                    'local'
                );
            }
            
            // Créer un message pour chaque destinataire
            foreach ($request->destinataires as $destinataireId) {
                // Vérifier si le destinataire existe et est actif
                $destinataire = Utilisateur::find($destinataireId);
                if (!$destinataire || !$destinataire->actif) {
                    continue; // Ignorer les destinataires inactifs ou inexistants
                }
                
                $message = Message::create([
                    'expediteur_id' => $user->id,
                    'destinataire_id' => $destinataireId,
                    'sujet' => $request->sujet,
                    'contenu' => $request->contenu,
                    'piece_jointe' => $pieceJointePath,
                    'lu' => false,
                ]);
                
                $messages[] = $message;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'count' => count($messages),
                    'messages' => $messages
                ],
                'message' => count($messages) . ' message(s) envoyé(s) avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi des messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un message à tous les utilisateurs d'un rôle spécifique
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function envoyerParRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,enseignant,parent,eleve',
            'sujet' => 'required|string|max:100',
            'contenu' => 'required|string|max:5000',
            'piece_jointe' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar',
            'classe_id' => 'nullable|exists:classes,id', // Pour filtrer par classe si le rôle est élève ou parent
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $user = Auth::user();
            $messages = [];
            
            // Traiter la pièce jointe si elle existe
            $pieceJointePath = null;
            if ($request->hasFile('piece_jointe')) {
                $fichier = $request->file('piece_jointe');
                $extension = $fichier->getClientOriginalExtension();
                $nomFichier = Str::slug($request->sujet) . '-' . time() . '.' . $extension;
                
                $pieceJointePath = $fichier->storeAs(
                    'pieces_jointes/' . date('Y/m'),
                    $nomFichier,
                    'local'
                );
            }
            
            // Récupérer les destinataires selon le rôle
            $query = Utilisateur::where('role', $request->role)
                               ->where('actif', true)
                               ->where('id', '!=', $user->id); // Exclure l'expéditeur
            
            // Filtrer par classe si spécifié et si le rôle est élève ou parent
            if ($request->has('classe_id') && in_array($request->role, ['eleve', 'parent'])) {
                if ($request->role === 'eleve') {
                    $query->whereHas('eleve', function($q) use ($request) {
                        $q->where('classe_id', $request->classe_id);
                    });
                } else { // parent
                    $query->whereHas('parent.eleves', function($q) use ($request) {
                        $q->where('classe_id', $request->classe_id);
                    });
                }
            }
            
            $destinataires = $query->get();
            
            // Créer un message pour chaque destinataire
            foreach ($destinataires as $destinataire) {
                $message = Message::create([
                    'expediteur_id' => $user->id,
                    'destinataire_id' => $destinataire->id,
                    'sujet' => $request->sujet,
                    'contenu' => $request->contenu,
                    'piece_jointe' => $pieceJointePath,
                    'lu' => false,
                ]);
                
                $messages[] = $message;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'count' => count($messages),
                    'role' => $request->role,
                    'classe_id' => $request->classe_id ?? null
                ],
                'message' => count($messages) . ' message(s) envoyé(s) aux utilisateurs de rôle ' . $request->role
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi des messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un message spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $message = Message::with(['expediteur', 'destinataire'])->findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à voir ce message
            if ($message->expediteur_id !== $user->id && $message->destinataire_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à voir ce message'
                ], 403);
            }
            
            // Marquer comme lu si l'utilisateur est le destinataire et que le message n'est pas encore lu
            if ($message->destinataire_id === $user->id && !$message->lu) {
                $message->lu = true;
                $message->date_lecture = now();
                $message->save();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $message,
                'message' => 'Message récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message non trouvé'
            ], 404);
        }
    }

    /**
     * Marquer un message comme lu
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function marquerCommeLu($id)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($id);
            
            // Vérifier si l'utilisateur est le destinataire du message
            if ($message->destinataire_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à marquer ce message comme lu'
                ], 403);
            }
            
            // Marquer comme lu
            $message->lu = true;
            $message->date_lecture = now();
            $message->save();
            
            return response()->json([
                'status' => 'success',
                'data' => $message,
                'message' => 'Message marqué comme lu avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message non trouvé'
            ], 404);
        }
    }

    /**
     * Marquer tous les messages comme lus
     *
     * @return \Illuminate\Http\Response
     */
    public function marquerTousCommeLus()
    {
        try {
            $user = Auth::user();
            
            // Récupérer tous les messages non lus de l'utilisateur
            $count = Message::where('destinataire_id', $user->id)
                          ->where('lu', false)
                          ->update([
                              'lu' => true,
                              'date_lecture' => now()
                          ]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'count' => $count
                ],
                'message' => $count . ' message(s) marqué(s) comme lu(s) avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du marquage des messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un message
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à supprimer ce message
            if ($message->expediteur_id !== $user->id && $message->destinataire_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à supprimer ce message'
                ], 403);
            }
            
            // Si l'utilisateur est l'expéditeur, marquer comme supprimé par l'expéditeur
            if ($message->expediteur_id === $user->id) {
                $message->supprime_expediteur = true;
            }
            
            // Si l'utilisateur est le destinataire, marquer comme supprimé par le destinataire
            if ($message->destinataire_id === $user->id) {
                $message->supprime_destinataire = true;
            }
            
            // Si le message est supprimé par les deux parties, supprimer la pièce jointe et le message
            if ($message->supprime_expediteur && $message->supprime_destinataire) {
                // Supprimer la pièce jointe si elle existe
                if ($message->piece_jointe && Storage::disk('local')->exists($message->piece_jointe)) {
                    Storage::disk('local')->delete($message->piece_jointe);
                }
                
                // Supprimer définitivement le message
                $message->delete();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message supprimé définitivement'
                ]);
            } else {
                // Sauvegarder les changements
                $message->save();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message supprimé de votre boîte'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message non trouvé ou erreur lors de la suppression: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Télécharger la pièce jointe d'un message
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function telechargerPieceJointe($id)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à télécharger cette pièce jointe
            if ($message->expediteur_id !== $user->id && $message->destinataire_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à télécharger cette pièce jointe'
                ], 403);
            }
            
            // Vérifier si le message a une pièce jointe
            if (!$message->piece_jointe) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ce message n\'a pas de pièce jointe'
                ], 400);
            }
            
            // Vérifier si le fichier existe
            if (!Storage::disk('local')->exists($message->piece_jointe)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le fichier n\'existe pas sur le serveur'
                ], 404);
            }
            
            // Retourner le fichier
            return Storage::disk('local')->download(
                $message->piece_jointe,
                pathinfo($message->piece_jointe, PATHINFO_BASENAME)
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message non trouvé ou erreur lors du téléchargement: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtenir le nombre de messages non lus
     *
     * @return \Illuminate\Http\Response
     */
    public function compteurNonLus()
    {
        try {
            $user = Auth::user();
            
            $count = Message::where('destinataire_id', $user->id)
                          ->where('lu', false)
                          ->where('supprime_destinataire', false)
                          ->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'count' => $count
                ],
                'message' => 'Nombre de messages non lus récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du compteur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les conversations (groupées par utilisateur)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function conversations(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Récupérer les utilisateurs avec qui l'utilisateur connecté a échangé des messages
            $expediteurs = Message::where('destinataire_id', $user->id)
                                ->where('supprime_destinataire', false)
                                ->select('expediteur_id')
                                ->distinct()
                                ->pluck('expediteur_id');
            
            $destinataires = Message::where('expediteur_id', $user->id)
                                  ->where('supprime_expediteur', false)
                                  ->select('destinataire_id')
                                  ->distinct()
                                  ->pluck('destinataire_id');
            
            $userIds = $expediteurs->merge($destinataires)->unique();
            
            // Récupérer les informations des utilisateurs
            $users = Utilisateur::whereIn('id', $userIds)->get();
            
            $conversations = [];
            
            foreach ($users as $otherUser) {
                // Récupérer le dernier message échangé avec cet utilisateur
                $lastMessage = Message::where(function($query) use ($user, $otherUser) {
                                    $query->where(function($q) use ($user, $otherUser) {
                                        $q->where('expediteur_id', $user->id)
                                          ->where('destinataire_id', $otherUser->id)
                                          ->where('supprime_expediteur', false);
                                    })->orWhere(function($q) use ($user, $otherUser) {
                                        $q->where('expediteur_id', $otherUser->id)
                                          ->where('destinataire_id', $user->id)
                                          ->where('supprime_destinataire', false);
                                    });
                                })
                                ->orderBy('created_at', 'desc')
                                ->first();
                
                if ($lastMessage) {
                    // Compter les messages non lus de cet utilisateur
                    $nonLus = Message::where('expediteur_id', $otherUser->id)
                                   ->where('destinataire_id', $user->id)
                                   ->where('lu', false)
                                   ->where('supprime_destinataire', false)
                                   ->count();
                    
                    $conversations[] = [
                        'utilisateur' => $otherUser,
                        'dernier_message' => $lastMessage,
                        'non_lus' => $nonLus
                    ];
                }
            }
            
            // Trier les conversations par date du dernier message (plus récent en premier)
            usort($conversations, function($a, $b) {
                return $b['dernier_message']->created_at <=> $a['dernier_message']->created_at;
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $conversations,
                'message' => 'Conversations récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des conversations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les messages échangés avec un utilisateur spécifique
     *
     * @param  int  $userId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function conversation($userId, Request $request)
    {
        try {
            $user = Auth::user();
            
            // Vérifier si l'autre utilisateur existe
            $otherUser = Utilisateur::findOrFail($userId);
            
            // Récupérer les messages échangés entre les deux utilisateurs
            $query = Message::where(function($q) use ($user, $userId) {
                        $q->where(function($q2) use ($user, $userId) {
                            $q2->where('expediteur_id', $user->id)
                              ->where('destinataire_id', $userId)
                              ->where('supprime_expediteur', false);
                        })->orWhere(function($q2) use ($user, $userId) {
                            $q2->where('expediteur_id', $userId)
                              ->where('destinataire_id', $user->id)
                              ->where('supprime_destinataire', false);
                        });
                    });
            
            // Tri
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy('created_at', $sortDirection);
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $messages = $query->with(['expediteur', 'destinataire'])->paginate($perPage);
            
            // Marquer les messages non lus comme lus
            Message::where('expediteur_id', $userId)
                 ->where('destinataire_id', $user->id)
                 ->where('lu', false)
                 ->update([
                     'lu' => true,
                     'date_lecture' => now()
                 ]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'utilisateur' => $otherUser,
                    'messages' => $messages
                ],
                'message' => 'Conversation récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé ou erreur lors de la récupération de la conversation: ' . $e->getMessage()
            ], 404);
        }
    }
}