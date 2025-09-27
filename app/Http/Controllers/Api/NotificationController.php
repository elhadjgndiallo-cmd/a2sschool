<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Afficher la liste des notifications de l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('utilisateur_id', $user->id);
        
        // Filtrage par statut de lecture
        if ($request->has('lue')) {
            $query->where('lue', $request->boolean('lue'));
        }
        
        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $notifications = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'message' => 'Liste des notifications récupérée avec succès'
        ]);
    }

    /**
     * Créer une nouvelle notification
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'titre' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'type' => 'required|string|in:info,success,warning,danger',
            'lien' => 'nullable|string|max:255',
            'icone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $notification = Notification::create([
                'utilisateur_id' => $request->utilisateur_id,
                'titre' => $request->titre,
                'message' => $request->message,
                'type' => $request->type,
                'lien' => $request->lien,
                'icone' => $request->icone,
                'lue' => false,
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $notification,
                'message' => 'Notification créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de la notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer une notification à plusieurs utilisateurs
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function envoyerMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utilisateurs' => 'required|array|min:1',
            'utilisateurs.*' => 'required|exists:utilisateurs,id',
            'titre' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'type' => 'required|string|in:info,success,warning,danger',
            'lien' => 'nullable|string|max:255',
            'icone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $notifications = [];
            
            foreach ($request->utilisateurs as $utilisateurId) {
                $notification = Notification::create([
                    'utilisateur_id' => $utilisateurId,
                    'titre' => $request->titre,
                    'message' => $request->message,
                    'type' => $request->type,
                    'lien' => $request->lien,
                    'icone' => $request->icone,
                    'lue' => false,
                ]);
                
                $notifications[] = $notification;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'notifications' => $notifications,
                    'total' => count($notifications)
                ],
                'message' => 'Notifications envoyées avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi des notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer une notification à tous les utilisateurs d'un rôle spécifique
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function envoyerParRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,enseignant,parent,eleve',
            'titre' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'type' => 'required|string|in:info,success,warning,danger',
            'lien' => 'nullable|string|max:255',
            'icone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $utilisateurs = Utilisateur::where('role', $request->role)
                ->where('statut', 'actif')
                ->get();
            
            $notifications = [];
            
            foreach ($utilisateurs as $utilisateur) {
                $notification = Notification::create([
                    'utilisateur_id' => $utilisateur->id,
                    'titre' => $request->titre,
                    'message' => $request->message,
                    'type' => $request->type,
                    'lien' => $request->lien,
                    'icone' => $request->icone,
                    'lue' => false,
                ]);
                
                $notifications[] = $notification;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_utilisateurs' => count($utilisateurs),
                    'total_notifications' => count($notifications)
                ],
                'message' => 'Notifications envoyées avec succès à tous les utilisateurs du rôle ' . $request->role
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi des notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function marquerCommeLue($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('id', $id)
                ->where('utilisateur_id', $user->id)
                ->firstOrFail();
            
            $notification->lue = true;
            $notification->save();
            
            return response()->json([
                'status' => 'success',
                'data' => $notification,
                'message' => 'Notification marquée comme lue'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification non trouvée ou vous n\'êtes pas autorisé à y accéder'
            ], 404);
        }
    }

    /**
     * Marquer toutes les notifications de l'utilisateur comme lues
     *
     * @return \Illuminate\Http\Response
     */
    public function marquerToutesCommeLues()
    {
        try {
            $user = Auth::user();
            $count = Notification::where('utilisateur_id', $user->id)
                ->where('lue', false)
                ->update(['lue' => true]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_marquees' => $count
                ],
                'message' => $count . ' notifications marquées comme lues'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour des notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une notification
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('id', $id)
                ->where('utilisateur_id', $user->id)
                ->firstOrFail();
            
            $notification->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Notification supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification non trouvée ou vous n\'êtes pas autorisé à la supprimer'
            ], 404);
        }
    }

    /**
     * Supprimer toutes les notifications lues de l'utilisateur
     *
     * @return \Illuminate\Http\Response
     */
    public function supprimerToutesLues()
    {
        try {
            $user = Auth::user();
            $count = Notification::where('utilisateur_id', $user->id)
                ->where('lue', true)
                ->delete();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_supprimees' => $count
                ],
                'message' => $count . ' notifications supprimées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression des notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le nombre de notifications non lues pour l'utilisateur connecté
     *
     * @return \Illuminate\Http\Response
     */
    public function compteurNonLues()
    {
        try {
            $user = Auth::user();
            $count = Notification::where('utilisateur_id', $user->id)
                ->where('lue', false)
                ->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_non_lues' => $count
                ],
                'message' => 'Compteur de notifications non lues récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du compteur: ' . $e->getMessage()
            ], 500);
        }
    }
}