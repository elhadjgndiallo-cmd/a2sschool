<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ParentController extends Controller
{
    /**
     * Afficher la liste des parents
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Parents::with('utilisateur', 'eleves');
        
        // Filtrage par élève
        if ($request->has('eleve_id')) {
            $query->whereHas('eleves', function($q) use ($request) {
                $q->where('eleves.id', $request->eleve_id);
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
        $parents = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $parents,
            'message' => 'Liste des parents récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouveau parent
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
            'telephone' => 'required|string|max:20',
            'profession' => 'required|string|max:255',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'eleves' => 'sometimes|array',
            'eleves.*' => 'exists:eleves,id'
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
        $utilisateur->role = 'parent';
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

        // Créer le parent
        $parent = new Parents();
        $parent->utilisateur_id = $utilisateur->id;
        $parent->profession = $request->profession;
        $parent->save();
        
        // Associer les élèves si fournis
        if ($request->has('eleves') && is_array($request->eleves)) {
            $parent->eleves()->attach($request->eleves);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'parent' => $parent->load('utilisateur', 'eleves'),
            ],
            'message' => 'Parent créé avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'un parent spécifique
     *
     * @param  \App\Models\Parents  $parent
     * @return \Illuminate\Http\Response
     */
    public function show(Parents $parent)
    {
        $parent->load('utilisateur', 'eleves');
        
        return response()->json([
            'status' => 'success',
            'data' => $parent,
            'message' => 'Détails du parent récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'un parent
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Parents  $parent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Parents $parent)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:utilisateurs,email,' . $parent->utilisateur_id,
            'date_naissance' => 'sometimes|required|date',
            'lieu_naissance' => 'sometimes|required|string|max:255',
            'sexe' => 'sometimes|required|in:M,F',
            'adresse' => 'sometimes|required|string',
            'telephone' => 'sometimes|required|string|max:20',
            'profession' => 'sometimes|required|string|max:255',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'eleves' => 'sometimes|array',
            'eleves.*' => 'exists:eleves,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Mettre à jour l'utilisateur
        $utilisateur = $parent->utilisateur;
        
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

        // Mettre à jour le parent
        if ($request->has('profession')) {
            $parent->profession = $request->profession;
        }
        
        $parent->save();
        
        // Mettre à jour les élèves si nécessaire
        if ($request->has('eleves')) {
            $parent->eleves()->sync($request->eleves);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'parent' => $parent->fresh()->load('utilisateur', 'eleves'),
            ],
            'message' => 'Parent mis à jour avec succès'
        ]);
    }

    /**
     * Supprimer un parent
     *
     * @param  \App\Models\Parents  $parent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Parents $parent)
    {
        $utilisateur = $parent->utilisateur;
        
        // Supprimer la photo de profil si elle existe
        if ($utilisateur->photo_profil) {
            Storage::disk('public')->delete($utilisateur->photo_profil);
        }
        
        // Détacher tous les élèves
        $parent->eleves()->detach();
        
        // Supprimer le parent et l'utilisateur associé
        $parent->delete();
        $utilisateur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Parent supprimé avec succès'
        ]);
    }
    
    /**
     * Récupérer les élèves d'un parent
     *
     * @param  \App\Models\Parents  $parent
     * @return \Illuminate\Http\Response
     */
    public function eleves(Parents $parent)
    {
        $eleves = $parent->eleves()->with('classe')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $eleves,
            'message' => 'Élèves du parent récupérés avec succès'
        ]);
    }
    
    /**
     * Récupérer les paiements des élèves d'un parent
     *
     * @param  \App\Models\Parents  $parent
     * @return \Illuminate\Http\Response
     */
    public function paiements(Parents $parent)
    {
        $elevesIds = $parent->eleves()->pluck('eleves.id');
        $paiements = \App\Models\Paiement::whereIn('eleve_id', $elevesIds)
                                    ->with('eleve')
                                    ->orderBy('date_paiement', 'desc')
                                    ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $paiements,
            'message' => 'Paiements des élèves du parent récupérés avec succès'
        ]);
    }
}