<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EleveController extends Controller
{
    /**
     * Afficher la liste des élèves
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Eleve::with('classe', 'utilisateur');
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }
        
        // Recherche par nom ou prénom
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('utilisateur', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            });
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $eleves = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $eleves,
            'message' => 'Liste des élèves récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouvel élève
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:utilisateurs',
            'password' => 'required|string|min:8',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'adresse' => 'required|string',
            'telephone' => 'nullable|string|max:20',
            'classe_id' => 'required|exists:classes,id',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'parent_id' => 'nullable|exists:parents,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Créer l'utilisateur
        $utilisateur = new \App\Models\Utilisateur();
        $utilisateur->name = $request->prenom . ' ' . $request->nom;
        $utilisateur->email = $request->email;
        $utilisateur->password = bcrypt($request->password);
        $utilisateur->role = 'student';
        $utilisateur->nom = $request->nom;
        $utilisateur->prenom = $request->prenom;
        $utilisateur->date_naissance = $request->date_naissance;
        $utilisateur->lieu_naissance = $request->lieu_naissance;
        $utilisateur->sexe = $request->sexe;
        $utilisateur->adresse = $request->adresse;
        $utilisateur->telephone = $request->telephone;
        
        // Traitement de la photo de profil
        if ($request->hasFile('photo_profil')) {
            $path = $request->file('photo_profil')->store('photos_profil', 'public');
            $utilisateur->photo_profil = $path;
        }
        
        $utilisateur->save();

        // Créer l'élève
        $eleve = new Eleve();
        $eleve->utilisateur_id = $utilisateur->id;
        $eleve->classe_id = $request->classe_id;
        $eleve->parent_id = $request->parent_id;
        $eleve->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'eleve' => $eleve->load('classe', 'utilisateur'),
            ],
            'message' => 'Élève créé avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'un élève spécifique
     *
     * @param  \App\Models\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function show(Eleve $eleve)
    {
        $eleve->load('classe', 'utilisateur', 'parent');
        
        return response()->json([
            'status' => 'success',
            'data' => $eleve,
            'message' => 'Détails de l\'élève récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'un élève
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Eleve $eleve)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:utilisateurs,email,' . $eleve->utilisateur_id,
            'date_naissance' => 'sometimes|required|date',
            'lieu_naissance' => 'sometimes|required|string|max:255',
            'sexe' => 'sometimes|required|in:M,F',
            'adresse' => 'sometimes|required|string',
            'telephone' => 'nullable|string|max:20',
            'classe_id' => 'sometimes|required|exists:classes,id',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'parent_id' => 'nullable|exists:parents,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Mettre à jour l'utilisateur
        $utilisateur = $eleve->utilisateur;
        
        if ($request->has('nom')) {
            $utilisateur->nom = $request->nom;
        }
        
        if ($request->has('prenom')) {
            $utilisateur->prenom = $request->prenom;
        }
        
        if ($request->has('nom') || $request->has('prenom')) {
            $utilisateur->name = $utilisateur->prenom . ' ' . $utilisateur->nom;
        }
        
        if ($request->has('email')) {
            $utilisateur->email = $request->email;
        }
        
        if ($request->has('date_naissance')) {
            $utilisateur->date_naissance = $request->date_naissance;
        }
        
        if ($request->has('lieu_naissance')) {
            $utilisateur->lieu_naissance = $request->lieu_naissance;
        }
        
        if ($request->has('sexe')) {
            $utilisateur->sexe = $request->sexe;
        }
        
        if ($request->has('adresse')) {
            $utilisateur->adresse = $request->adresse;
        }
        
        if ($request->has('telephone')) {
            $utilisateur->telephone = $request->telephone;
        }
        
        // Traitement de la photo de profil
        if ($request->hasFile('photo_profil')) {
            // Supprimer l'ancienne photo si elle existe
            if ($utilisateur->photo_profil) {
                Storage::disk('public')->delete($utilisateur->photo_profil);
            }
            
            $path = $request->file('photo_profil')->store('photos_profil', 'public');
            $utilisateur->photo_profil = $path;
        }
        
        $utilisateur->save();

        // Mettre à jour l'élève
        if ($request->has('classe_id')) {
            $eleve->classe_id = $request->classe_id;
        }
        
        if ($request->has('parent_id')) {
            $eleve->parent_id = $request->parent_id;
        }
        
        $eleve->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'eleve' => $eleve->fresh()->load('classe', 'utilisateur', 'parent'),
            ],
            'message' => 'Élève mis à jour avec succès'
        ]);
    }

    /**
     * Supprimer un élève
     *
     * @param  \App\Models\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function destroy(Eleve $eleve)
    {
        $utilisateur = $eleve->utilisateur;
        
        // Supprimer la photo de profil si elle existe
        if ($utilisateur->photo_profil) {
            Storage::disk('public')->delete($utilisateur->photo_profil);
        }
        
        // Supprimer l'élève et l'utilisateur associé
        $eleve->delete();
        $utilisateur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Élève supprimé avec succès'
        ]);
    }
    
    /**
     * Récupérer les notes d'un élève
     *
     * @param  \App\Models\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function notes(Eleve $eleve)
    {
        $notes = $eleve->notes()->with('matiere')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $notes,
            'message' => 'Notes de l\'élève récupérées avec succès'
        ]);
    }
    
    /**
     * Récupérer les absences d'un élève
     *
     * @param  \App\Models\Eleve  $eleve
     * @return \Illuminate\Http\Response
     */
    public function absences(Eleve $eleve)
    {
        $absences = $eleve->absences()->with('matiere')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $absences,
            'message' => 'Absences de l\'élève récupérées avec succès'
        ]);
    }
}