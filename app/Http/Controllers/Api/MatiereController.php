<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatiereController extends Controller
{
    /**
     * Afficher la liste des matières
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Matiere::with('enseignants');
        
        // Filtrage par niveau
        if ($request->has('niveau')) {
            $query->where('niveau', $request->niveau);
        }
        
        // Recherche par nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nom', 'like', "%{$search}%");
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $matieres = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $matieres,
            'message' => 'Liste des matières récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer une nouvelle matière
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:matieres',
            'coefficient' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'niveau' => 'nullable|string|max:50',
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

        $matiere = new Matiere();
        $matiere->nom = $request->nom;
        $matiere->code = $request->code;
        $matiere->coefficient = $request->coefficient;
        $matiere->description = $request->description;
        $matiere->niveau = $request->niveau;
        $matiere->save();
        
        // Associer les enseignants si fournis
        if ($request->has('enseignants') && is_array($request->enseignants)) {
            $matiere->enseignants()->attach($request->enseignants);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'matiere' => $matiere->load('enseignants'),
            ],
            'message' => 'Matière créée avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'une matière spécifique
     *
     * @param  \App\Models\Matiere  $matiere
     * @return \Illuminate\Http\Response
     */
    public function show(Matiere $matiere)
    {
        $matiere->load('enseignants');
        
        return response()->json([
            'status' => 'success',
            'data' => $matiere,
            'message' => 'Détails de la matière récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'une matière
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Matiere  $matiere
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Matiere $matiere)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:matieres,code,' . $matiere->id,
            'coefficient' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'niveau' => 'nullable|string|max:50',
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
            $matiere->nom = $request->nom;
        }
        
        if ($request->has('code')) {
            $matiere->code = $request->code;
        }
        
        if ($request->has('coefficient')) {
            $matiere->coefficient = $request->coefficient;
        }
        
        if ($request->has('description')) {
            $matiere->description = $request->description;
        }
        
        if ($request->has('niveau')) {
            $matiere->niveau = $request->niveau;
        }
        
        $matiere->save();
        
        // Mettre à jour les enseignants si nécessaire
        if ($request->has('enseignants')) {
            $matiere->enseignants()->sync($request->enseignants);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'matiere' => $matiere->fresh()->load('enseignants'),
            ],
            'message' => 'Matière mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une matière
     *
     * @param  \App\Models\Matiere  $matiere
     * @return \Illuminate\Http\Response
     */
    public function destroy(Matiere $matiere)
    {
        // Vérifier si la matière a des notes associées
        if ($matiere->notes()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer cette matière car elle est associée à des notes'
            ], 400);
        }
        
        // Détacher tous les enseignants
        $matiere->enseignants()->detach();
        
        // Supprimer la matière
        $matiere->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Matière supprimée avec succès'
        ]);
    }
    
    /**
     * Récupérer les enseignants d'une matière
     *
     * @param  \App\Models\Matiere  $matiere
     * @return \Illuminate\Http\Response
     */
    public function enseignants(Matiere $matiere)
    {
        $enseignants = $matiere->enseignants()->with('utilisateur')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $enseignants,
            'message' => 'Enseignants de la matière récupérés avec succès'
        ]);
    }
    
    /**
     * Récupérer les notes d'une matière
     *
     * @param  \App\Models\Matiere  $matiere
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notes(Matiere $matiere, Request $request)
    {
        $query = $matiere->notes()->with('eleve.utilisateur');
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtrage par trimestre
        if ($request->has('trimestre')) {
            $query->where('trimestre', $request->trimestre);
        }
        
        // Filtrage par type d'évaluation
        if ($request->has('type_evaluation')) {
            $query->where('type_evaluation', $request->type_evaluation);
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $notes = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $notes,
            'message' => 'Notes de la matière récupérées avec succès'
        ]);
    }
    
    /**
     * Récupérer les statistiques d'une matière
     *
     * @param  \App\Models\Matiere  $matiere
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistiques(Matiere $matiere, Request $request)
    {
        $query = $matiere->notes();
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtrage par trimestre
        if ($request->has('trimestre')) {
            $query->where('trimestre', $request->trimestre);
        }
        
        $notes = $query->get();
        
        // Calcul des statistiques
        $moyenne = 0;
        $min = 20;
        $max = 0;
        $total = $notes->count();
        $reussite = 0;
        
        if ($total > 0) {
            $somme = 0;
            
            foreach ($notes as $note) {
                $somme += $note->valeur;
                
                if ($note->valeur < $min) {
                    $min = $note->valeur;
                }
                
                if ($note->valeur > $max) {
                    $max = $note->valeur;
                }
                
                if ($note->valeur >= 10) {
                    $reussite++;
                }
            }
            
            $moyenne = $somme / $total;
        } else {
            $min = 0;
        }
        
        $tauxReussite = $total > 0 ? ($reussite / $total) * 100 : 0;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'moyenne' => round($moyenne, 2),
                'min' => $min,
                'max' => $max,
                'total_notes' => $total,
                'reussite' => $reussite,
                'taux_reussite' => round($tauxReussite, 2)
            ],
            'message' => 'Statistiques de la matière récupérées avec succès'
        ]);
    }
}