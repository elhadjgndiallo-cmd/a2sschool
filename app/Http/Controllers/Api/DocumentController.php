<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Afficher la liste des documents
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Document::query();
        
        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filtrage par catégorie
        if ($request->has('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        
        // Filtrage par visibilité
        if ($request->has('public')) {
            $query->where('public', $request->boolean('public'));
        }
        
        // Filtrage par classe (si spécifié)
        if ($request->has('classe_id')) {
            $query->where(function($q) use ($request) {
                $q->where('classe_id', $request->classe_id)
                  ->orWhereNull('classe_id');
            });
        }
        
        // Recherche par titre ou description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $documents = $query->with(['createur', 'classe'])->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $documents,
            'message' => 'Liste des documents récupérée avec succès'
        ]);
    }

    /**
     * Télécharger un document
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function telecharger($id)
    {
        try {
            $document = Document::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à télécharger ce document
            if (!$document->public) {
                $user = Auth::user();
                $userRole = $user->role;
                
                // Si le document est lié à une classe et que l'utilisateur n'est pas admin, enseignant de cette classe ou parent/élève de cette classe
                if ($document->classe_id && $userRole !== 'admin') {
                    $authorized = false;
                    
                    if ($userRole === 'enseignant') {
                        $authorized = $user->enseignant->classes()->where('classes.id', $document->classe_id)->exists();
                    } elseif ($userRole === 'parent') {
                        $authorized = $user->parent->eleves()->whereHas('classe', function($q) use ($document) {
                            $q->where('id', $document->classe_id);
                        })->exists();
                    } elseif ($userRole === 'eleve') {
                        $authorized = $user->eleve->classe_id === $document->classe_id;
                    }
                    
                    if (!$authorized) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Vous n\'êtes pas autorisé à télécharger ce document'
                        ], 403);
                    }
                }
            }
            
            // Vérifier si le fichier existe
            if (!Storage::disk('documents')->exists($document->chemin)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le fichier n\'existe pas sur le serveur'
                ], 404);
            }
            
            // Incrémenter le compteur de téléchargements
            $document->increment('telechargements');
            
            // Retourner le fichier
            return Storage::disk('documents')->download(
                $document->chemin,
                $document->titre . '.' . pathinfo($document->chemin, PATHINFO_EXTENSION)
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé ou erreur lors du téléchargement: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Téléverser un nouveau document
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:cours,devoir,examen,administratif,autre',
            'categorie' => 'nullable|string|max:50',
            'public' => 'boolean',
            'classe_id' => 'nullable|exists:classes,id',
            'fichier' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,jpg,jpeg,png,gif',
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
            
            // Générer un nom de fichier unique
            $fichier = $request->file('fichier');
            $extension = $fichier->getClientOriginalExtension();
            $nomFichier = Str::slug($request->titre) . '-' . time() . '.' . $extension;
            
            // Stocker le fichier
            $chemin = $fichier->storeAs(
                $request->type . '/' . date('Y/m'),
                $nomFichier,
                'documents'
            );
            
            // Créer l'enregistrement dans la base de données
            $document = Document::create([
                'titre' => $request->titre,
                'description' => $request->description,
                'type' => $request->type,
                'categorie' => $request->categorie,
                'chemin' => $chemin,
                'taille' => $fichier->getSize(),
                'format' => $extension,
                'public' => $request->boolean('public', true),
                'classe_id' => $request->classe_id,
                'createur_id' => $user->id,
            ]);
            
            // Charger les relations
            $document->load(['createur', 'classe']);
            
            return response()->json([
                'status' => 'success',
                'data' => $document,
                'message' => 'Document téléversé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du téléversement du document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un document spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $document = Document::with(['createur', 'classe'])->findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à voir ce document
            if (!$document->public) {
                $user = Auth::user();
                $userRole = $user->role;
                
                // Si le document est lié à une classe et que l'utilisateur n'est pas admin, enseignant de cette classe ou parent/élève de cette classe
                if ($document->classe_id && $userRole !== 'admin') {
                    $authorized = false;
                    
                    if ($userRole === 'enseignant') {
                        $authorized = $user->enseignant->classes()->where('classes.id', $document->classe_id)->exists();
                    } elseif ($userRole === 'parent') {
                        $authorized = $user->parent->eleves()->whereHas('classe', function($q) use ($document) {
                            $q->where('id', $document->classe_id);
                        })->exists();
                    } elseif ($userRole === 'eleve') {
                        $authorized = $user->eleve->classe_id === $document->classe_id;
                    }
                    
                    if (!$authorized) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Vous n\'êtes pas autorisé à voir ce document'
                        ], 403);
                    }
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $document,
                'message' => 'Document récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé'
            ], 404);
        }
    }

    /**
     * Mettre à jour un document spécifique
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à modifier ce document
            $user = Auth::user();
            if ($user->role !== 'admin' && $document->createur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à modifier ce document'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'titre' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string|max:500',
                'type' => 'sometimes|required|string|in:cours,devoir,examen,administratif,autre',
                'categorie' => 'nullable|string|max:50',
                'public' => 'boolean',
                'classe_id' => 'nullable|exists:classes,id',
                'fichier' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,jpg,jpeg,png,gif',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                    'message' => 'Erreur de validation des données'
                ], 422);
            }

            // Mettre à jour les champs de base
            $document->titre = $request->titre ?? $document->titre;
            $document->description = $request->description ?? $document->description;
            $document->type = $request->type ?? $document->type;
            $document->categorie = $request->categorie ?? $document->categorie;
            $document->public = $request->has('public') ? $request->boolean('public') : $document->public;
            $document->classe_id = $request->classe_id ?? $document->classe_id;
            
            // Si un nouveau fichier est fourni, le traiter
            if ($request->hasFile('fichier')) {
                // Supprimer l'ancien fichier
                if (Storage::disk('documents')->exists($document->chemin)) {
                    Storage::disk('documents')->delete($document->chemin);
                }
                
                // Générer un nom de fichier unique
                $fichier = $request->file('fichier');
                $extension = $fichier->getClientOriginalExtension();
                $nomFichier = Str::slug($request->titre ?? $document->titre) . '-' . time() . '.' . $extension;
                
                // Stocker le nouveau fichier
                $chemin = $fichier->storeAs(
                    ($request->type ?? $document->type) . '/' . date('Y/m'),
                    $nomFichier,
                    'documents'
                );
                
                // Mettre à jour les informations du fichier
                $document->chemin = $chemin;
                $document->taille = $fichier->getSize();
                $document->format = $extension;
            }
            
            $document->save();
            
            // Recharger les relations
            $document->load(['createur', 'classe']);
            
            return response()->json([
                'status' => 'success',
                'data' => $document,
                'message' => 'Document mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé ou erreur lors de la mise à jour: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Supprimer un document spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à supprimer ce document
            $user = Auth::user();
            if ($user->role !== 'admin' && $document->createur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à supprimer ce document'
                ], 403);
            }
            
            // Supprimer le fichier physique
            if (Storage::disk('documents')->exists($document->chemin)) {
                Storage::disk('documents')->delete($document->chemin);
            }
            
            // Supprimer l'enregistrement de la base de données
            $document->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Document supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé ou erreur lors de la suppression: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Récupérer les documents pour une classe spécifique
     *
     * @param  int  $classeId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function documentsClasse($classeId, Request $request)
    {
        try {
            $query = Document::where(function($q) use ($classeId) {
                $q->where('classe_id', $classeId)
                  ->orWhere(function($q2) {
                      $q2->whereNull('classe_id')
                         ->where('public', true);
                  });
            });
            
            // Filtrage par type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filtrage par catégorie
            if ($request->has('categorie')) {
                $query->where('categorie', $request->categorie);
            }
            
            // Recherche par titre ou description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Tri
            $sortField = $request->input('sort_field', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $documents = $query->with(['createur', 'classe'])->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $documents,
                'message' => 'Documents de la classe récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les documents pour l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mesDocuments(Request $request)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role;
            
            $query = Document::query();
            
            // Filtrer selon le rôle de l'utilisateur
            if ($userRole === 'admin') {
                // L'admin voit tous les documents
            } elseif ($userRole === 'enseignant') {
                $classeIds = $user->enseignant->classes()->pluck('classes.id')->toArray();
                $query->where(function($q) use ($user, $classeIds) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents créés par l'utilisateur
                    $q->orWhere('createur_id', $user->id);
                    // Documents liés aux classes de l'enseignant
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                });
            } elseif ($userRole === 'parent') {
                $classeIds = $user->parent->eleves()->pluck('classe_id')->unique()->filter()->toArray();
                $query->where(function($q) use ($classeIds) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents liés aux classes des enfants
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                });
            } elseif ($userRole === 'eleve') {
                $classeId = $user->eleve->classe_id;
                $query->where(function($q) use ($classeId) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents liés à la classe de l'élève
                    if ($classeId) {
                        $q->orWhere('classe_id', $classeId);
                    }
                });
            }
            
            // Filtrage par type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filtrage par catégorie
            if ($request->has('categorie')) {
                $query->where('categorie', $request->categorie);
            }
            
            // Recherche par titre ou description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Tri
            $sortField = $request->input('sort_field', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $documents = $query->with(['createur', 'classe'])->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $documents,
                'message' => 'Mes documents récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les documents récemment ajoutés
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function documentsRecents(Request $request)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role;
            
            $query = Document::query();
            
            // Filtrer selon le rôle de l'utilisateur
            if ($userRole === 'admin') {
                // L'admin voit tous les documents
            } elseif ($userRole === 'enseignant') {
                $classeIds = $user->enseignant->classes()->pluck('classes.id')->toArray();
                $query->where(function($q) use ($user, $classeIds) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents créés par l'utilisateur
                    $q->orWhere('createur_id', $user->id);
                    // Documents liés aux classes de l'enseignant
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                });
            } elseif ($userRole === 'parent') {
                $classeIds = $user->parent->eleves()->pluck('classe_id')->unique()->filter()->toArray();
                $query->where(function($q) use ($classeIds) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents liés aux classes des enfants
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                });
            } elseif ($userRole === 'eleve') {
                $classeId = $user->eleve->classe_id;
                $query->where(function($q) use ($classeId) {
                    // Documents publics
                    $q->where('public', true);
                    // Documents liés à la classe de l'élève
                    if ($classeId) {
                        $q->orWhere('classe_id', $classeId);
                    }
                });
            }
            
            // Limiter aux documents récents (derniers jours)
            $jours = $request->input('jours', 30);
            $query->where('created_at', '>=', now()->subDays($jours));
            
            // Limiter le nombre de documents
            $limit = $request->input('limit', 5);
            $query->orderBy('created_at', 'desc')->limit($limit);
            
            $documents = $query->with(['createur', 'classe'])->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $documents,
                'message' => 'Documents récents récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents: ' . $e->getMessage()
            ], 500);
        }
    }
}