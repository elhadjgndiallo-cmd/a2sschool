<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClasseController extends Controller
{
    /**
     * Afficher la liste des classes
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Classe::with('enseignants', 'eleves');
        
        // Filtrage par niveau
        if ($request->has('niveau')) {
            $query->where('niveau', $request->niveau);
        }
        
        // Filtrage par année scolaire
        if ($request->has('annee_scolaire')) {
            $query->where('annee_scolaire', $request->annee_scolaire);
        }
        
        // Recherche par nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nom', 'like', "%{$search}%");
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $classes = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $classes,
            'message' => 'Liste des classes récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer une nouvelle classe
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'niveau' => 'required|string|max:50',
            'annee_scolaire' => 'required|string|max:20',
            'capacite' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'enseignants' => 'sometimes|array',
            'enseignants.*' => 'exists:enseignants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $classe = new Classe();
        $classe->nom = $request->nom;
        $classe->niveau = $request->niveau;
        $classe->annee_scolaire = $request->annee_scolaire;
        $classe->capacite = $request->capacite;
        $classe->description = $request->description;
        $classe->save();
        
        // Associer les enseignants si fournis
        if ($request->has('enseignants') && is_array($request->enseignants)) {
            $classe->enseignants()->attach($request->enseignants);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'classe' => $classe->load('enseignants', 'eleves'),
            ],
            'message' => 'Classe créée avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'une classe spécifique
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function show(Classe $classe)
    {
        $classe->load('enseignants', 'eleves');
        
        return response()->json([
            'status' => 'success',
            'data' => $classe,
            'message' => 'Détails de la classe récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'une classe
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Classe $classe)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'niveau' => 'sometimes|required|string|max:50',
            'annee_scolaire' => 'sometimes|required|string|max:20',
            'capacite' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'enseignants' => 'sometimes|array',
            'enseignants.*' => 'exists:enseignants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        if ($request->has('nom')) {
            $classe->nom = $request->nom;
        }
        
        if ($request->has('niveau')) {
            $classe->niveau = $request->niveau;
        }
        
        if ($request->has('annee_scolaire')) {
            $classe->annee_scolaire = $request->annee_scolaire;
        }
        
        if ($request->has('capacite')) {
            $classe->capacite = $request->capacite;
        }
        
        if ($request->has('description')) {
            $classe->description = $request->description;
        }
        
        $classe->save();
        
        // Mettre à jour les enseignants si nécessaire
        if ($request->has('enseignants')) {
            $classe->enseignants()->sync($request->enseignants);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'classe' => $classe->fresh()->load('enseignants', 'eleves'),
            ],
            'message' => 'Classe mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une classe
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classe $classe)
    {
        // Vérifier si la classe a des élèves
        if ($classe->eleves()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer cette classe car elle contient des élèves'
            ], 400);
        }
        
        // Détacher tous les enseignants
        $classe->enseignants()->detach();
        
        // Supprimer la classe
        $classe->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Classe supprimée avec succès'
        ]);
    }
    
    /**
     * Récupérer les élèves d'une classe
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function eleves(Classe $classe)
    {
        $eleves = $classe->eleves()->with('utilisateur', 'parents')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $eleves,
            'message' => 'Élèves de la classe récupérés avec succès'
        ]);
    }
    
    /**
     * Récupérer les enseignants d'une classe
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function enseignants(Classe $classe)
    {
        $enseignants = $classe->enseignants()->with('utilisateur', 'matieres')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $enseignants,
            'message' => 'Enseignants de la classe récupérés avec succès'
        ]);
    }
    
    /**
     * Récupérer l'emploi du temps d'une classe
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function emploiDuTemps(Classe $classe)
    {
        // Cette méthode est un exemple et devrait être adaptée selon votre modèle de données
        // pour l'emploi du temps
        $emploiDuTemps = [];
        
        return response()->json([
            'status' => 'success',
            'data' => $emploiDuTemps,
            'message' => 'Emploi du temps de la classe récupéré avec succès'
        ]);
    }
    
    /**
     * Récupérer les statistiques d'une classe
     *
     * @param  \App\Models\Classe  $classe
     * @return \Illuminate\Http\Response
     */
    public function statistiques(Classe $classe)
    {
        $totalEleves = $classe->eleves()->count();
        $totalGarcons = $classe->eleves()->whereHas('utilisateur', function($q) {
            $q->where('sexe', 'M');
        })->count();
        $totalFilles = $classe->eleves()->whereHas('utilisateur', function($q) {
            $q->where('sexe', 'F');
        })->count();
        
        // Calcul de la moyenne générale de la classe
        $moyenneGenerale = 0;
        $eleves = $classe->eleves;
        
        if ($eleves->count() > 0) {
            $sommeNotes = 0;
            $nombreNotes = 0;
            
            foreach ($eleves as $eleve) {
                $notes = $eleve->notes;
                
                foreach ($notes as $note) {
                    $sommeNotes += $note->valeur;
                    $nombreNotes++;
                }
            }
            
            if ($nombreNotes > 0) {
                $moyenneGenerale = $sommeNotes / $nombreNotes;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_eleves' => $totalEleves,
                'total_garcons' => $totalGarcons,
                'total_filles' => $totalFilles,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'capacite' => $classe->capacite,
                'places_disponibles' => $classe->capacite - $totalEleves
            ],
            'message' => 'Statistiques de la classe récupérées avec succès'
        ]);
    }
}