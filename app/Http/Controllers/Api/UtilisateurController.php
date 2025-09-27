<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UtilisateurController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Utilisateur::query();
        
        // Filtrage par nom
        if ($request->has('nom')) {
            $query->where('nom', 'like', '%' . $request->nom . '%');
        }
        
        // Filtrage par prénom
        if ($request->has('prenom')) {
            $query->where('prenom', 'like', '%' . $request->prenom . '%');
        }
        
        // Filtrage par email
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        // Filtrage par rôle
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        // Filtrage par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Tri par nom
        $query->orderBy('nom', 'asc')->orderBy('prenom', 'asc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $utilisateurs = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $utilisateurs,
            'message' => 'Liste des utilisateurs récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouvel utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:utilisateurs',
            'telephone' => 'required|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'genre' => 'required|string|in:M,F',
            'role' => 'required|string|in:admin,enseignant,parent,eleve',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $utilisateur = new Utilisateur();
        $utilisateur->nom = $request->nom;
        $utilisateur->prenom = $request->prenom;
        $utilisateur->email = $request->email;
        $utilisateur->telephone = $request->telephone;
        $utilisateur->adresse = $request->adresse;
        $utilisateur->date_naissance = $request->date_naissance;
        $utilisateur->genre = $request->genre;
        $utilisateur->role = $request->role;
        $utilisateur->password = Hash::make($request->password);
        $utilisateur->statut = 'actif';
        
        // Traitement de la photo de profil
        if ($request->hasFile('photo_profile')) {
            $photo = $request->file('photo_profile');
            $filename = 'user_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/photos/utilisateurs', $filename);
            $utilisateur->photo_profile = 'photos/utilisateurs/' . $filename;
        }
        
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur,
            ],
            'message' => 'Utilisateur créé avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'un utilisateur spécifique
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function show(Utilisateur $utilisateur)
    {
        // Charger les relations en fonction du rôle
        switch ($utilisateur->role) {
            case 'enseignant':
                $utilisateur->load('enseignant.matieres', 'enseignant.classes');
                break;
            case 'eleve':
                $utilisateur->load('eleve.classe', 'eleve.parents');
                break;
            case 'parent':
                $utilisateur->load('parent.eleves');
                break;
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $utilisateur,
            'message' => 'Détails de l\'utilisateur récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'un utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Utilisateur $utilisateur)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|string|email|max:100|unique:utilisateurs,email,' . $utilisateur->id,
            'telephone' => 'sometimes|required|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'genre' => 'sometimes|required|string|in:M,F',
            'role' => 'sometimes|required|string|in:admin,enseignant,parent,eleve',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        if ($request->has('nom')) {
            $utilisateur->nom = $request->nom;
        }
        
        if ($request->has('prenom')) {
            $utilisateur->prenom = $request->prenom;
        }
        
        if ($request->has('email')) {
            $utilisateur->email = $request->email;
        }
        
        if ($request->has('telephone')) {
            $utilisateur->telephone = $request->telephone;
        }
        
        if ($request->has('adresse')) {
            $utilisateur->adresse = $request->adresse;
        }
        
        if ($request->has('date_naissance')) {
            $utilisateur->date_naissance = $request->date_naissance;
        }
        
        if ($request->has('genre')) {
            $utilisateur->genre = $request->genre;
        }
        
        if ($request->has('role')) {
            $utilisateur->role = $request->role;
        }
        
        if ($request->has('password')) {
            $utilisateur->password = Hash::make($request->password);
        }
        
        // Traitement de la photo de profil
        if ($request->hasFile('photo_profile')) {
            // Supprimer l'ancienne photo si elle existe
            if ($utilisateur->photo_profile) {
                Storage::delete('public/' . $utilisateur->photo_profile);
            }
            
            $photo = $request->file('photo_profile');
            $filename = 'user_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/photos/utilisateurs', $filename);
            $utilisateur->photo_profile = 'photos/utilisateurs/' . $filename;
        }
        
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->fresh(),
            ],
            'message' => 'Utilisateur mis à jour avec succès'
        ]);
    }

    /**
     * Supprimer un utilisateur
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function destroy(Utilisateur $utilisateur)
    {
        // Vérifier si l'utilisateur a des relations qui empêchent sa suppression
        if ($utilisateur->role === 'enseignant' && $utilisateur->enseignant) {
            if ($utilisateur->enseignant->notes()->count() > 0 || $utilisateur->enseignant->absences()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer cet utilisateur car il a des notes ou des absences associées'
                ], 400);
            }
            
            // Détacher les matières et les classes avant de supprimer
            $utilisateur->enseignant->matieres()->detach();
            $utilisateur->enseignant->classes()->detach();
            $utilisateur->enseignant->delete();
        }
        
        if ($utilisateur->role === 'eleve' && $utilisateur->eleve) {
            if ($utilisateur->eleve->notes()->count() > 0 || $utilisateur->eleve->absences()->count() > 0 || $utilisateur->eleve->paiements()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer cet utilisateur car il a des notes, des absences ou des paiements associés'
                ], 400);
            }
            
            // Détacher les parents avant de supprimer
            $utilisateur->eleve->parents()->detach();
            $utilisateur->eleve->delete();
        }
        
        if ($utilisateur->role === 'parent' && $utilisateur->parent) {
            // Détacher les élèves avant de supprimer
            $utilisateur->parent->eleves()->detach();
            $utilisateur->parent->delete();
        }
        
        // Supprimer la photo de profil si elle existe
        if ($utilisateur->photo_profile) {
            Storage::delete('public/' . $utilisateur->photo_profile);
        }
        
        $utilisateur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
    
    /**
     * Changer le statut d'un utilisateur (activer/désactiver)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function changerStatut(Request $request, Utilisateur $utilisateur)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|string|in:actif,inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $utilisateur->statut = $request->statut;
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->fresh(),
            ],
            'message' => 'Statut de l\'utilisateur mis à jour avec succès'
        ]);
    }
    
    /**
     * Changer le mot de passe d'un utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function changerMotDePasse(Request $request, Utilisateur $utilisateur)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $utilisateur->password = Hash::make($request->password);
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe de l\'utilisateur mis à jour avec succès'
        ]);
    }
    
    /**
     * Supprimer la photo de profil d'un utilisateur
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Http\Response
     */
    public function supprimerPhoto(Utilisateur $utilisateur)
    {
        if (!$utilisateur->photo_profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cet utilisateur n\'a pas de photo de profil'
            ], 400);
        }
        
        // Supprimer la photo de profil
        Storage::delete('public/' . $utilisateur->photo_profile);
        
        $utilisateur->photo_profile = null;
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur->fresh(),
            ],
            'message' => 'Photo de profil supprimée avec succès'
        ]);
    }
}