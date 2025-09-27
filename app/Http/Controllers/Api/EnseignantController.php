<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EnseignantController extends Controller
{
    /**
     * Afficher la liste des enseignants
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Enseignant::with('utilisateur', 'matieres');
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->whereHas('matieres', function($q) use ($request) {
                $q->where('matieres.id', $request->matiere_id);
            });
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
        $enseignants = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $enseignants,
            'message' => 'Liste des enseignants récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouvel enseignant
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
            'specialite' => 'required|string|max:255',
            'date_embauche' => 'required|date',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'matieres' => 'required|array',
            'matieres.*' => 'exists:matieres,id'
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
        $utilisateur->role = 'teacher';
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

        // Créer l'enseignant
        $enseignant = new Enseignant();
        $enseignant->utilisateur_id = $utilisateur->id;
        $enseignant->specialite = $request->specialite;
        $enseignant->date_embauche = $request->date_embauche;
        $enseignant->save();
        
        // Associer les matières
        $enseignant->matieres()->attach($request->matieres);

        return response()->json([
            'status' => 'success',
            'data' => [
                'enseignant' => $enseignant->load('utilisateur', 'matieres'),
            ],
            'message' => 'Enseignant créé avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'un enseignant spécifique
     *
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function show(Enseignant $enseignant)
    {
        $enseignant->load('utilisateur', 'matieres', 'classes');
        
        return response()->json([
            'status' => 'success',
            'data' => $enseignant,
            'message' => 'Détails de l\'enseignant récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'un enseignant
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Enseignant $enseignant)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:utilisateurs,email,' . $enseignant->utilisateur_id,
            'date_naissance' => 'sometimes|required|date',
            'lieu_naissance' => 'sometimes|required|string|max:255',
            'sexe' => 'sometimes|required|in:M,F',
            'adresse' => 'sometimes|required|string',
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'sometimes|required|string|max:255',
            'date_embauche' => 'sometimes|required|date',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'matieres' => 'sometimes|required|array',
            'matieres.*' => 'exists:matieres,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Mettre à jour l'utilisateur
        $utilisateur = $enseignant->utilisateur;
        
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

        // Mettre à jour l'enseignant
        if ($request->has('specialite')) {
            $enseignant->specialite = $request->specialite;
        }
        
        if ($request->has('date_embauche')) {
            $enseignant->date_embauche = $request->date_embauche;
        }
        
        $enseignant->save();
        
        // Mettre à jour les matières si nécessaire
        if ($request->has('matieres')) {
            $enseignant->matieres()->sync($request->matieres);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'enseignant' => $enseignant->fresh()->load('utilisateur', 'matieres', 'classes'),
            ],
            'message' => 'Enseignant mis à jour avec succès'
        ]);
    }

    /**
     * Supprimer un enseignant
     *
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Enseignant $enseignant)
    {
        $utilisateur = $enseignant->utilisateur;
        
        // Supprimer la photo de profil si elle existe
        if ($utilisateur->photo_profil) {
            Storage::disk('public')->delete($utilisateur->photo_profil);
        }
        
        // Détacher toutes les matières
        $enseignant->matieres()->detach();
        
        // Supprimer l'enseignant et l'utilisateur associé
        $enseignant->delete();
        $utilisateur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Enseignant supprimé avec succès'
        ]);
    }
    
    /**
     * Récupérer les classes d'un enseignant
     *
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function classes(Enseignant $enseignant)
    {
        $classes = $enseignant->classes;
        
        return response()->json([
            'status' => 'success',
            'data' => $classes,
            'message' => 'Classes de l\'enseignant récupérées avec succès'
        ]);
    }
    
    /**
     * Récupérer l'emploi du temps d'un enseignant
     *
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Http\Response
     */
    public function emploiDuTemps(Enseignant $enseignant)
    {
        // Cette méthode est un exemple et devrait être adaptée selon votre modèle de données
        // pour l'emploi du temps
        $emploiDuTemps = [];
        
        return response()->json([
            'status' => 'success',
            'data' => $emploiDuTemps,
            'message' => 'Emploi du temps de l\'enseignant récupéré avec succès'
        ]);
    }
}