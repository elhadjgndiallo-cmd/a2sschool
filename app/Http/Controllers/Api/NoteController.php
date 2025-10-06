<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Afficher la liste des notes
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Note::with('eleve.utilisateur', 'matiere', 'enseignant.utilisateur');
        
        // Filtrage par élève
        if ($request->has('eleve_id')) {
            $query->where('eleve_id', $request->eleve_id);
        }
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }
        
        // Filtrage par enseignant
        if ($request->has('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }
        
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
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_evaluation', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_evaluation', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_evaluation', '<=', $request->date_fin);
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $notes = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $notes,
            'message' => 'Liste des notes récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer une nouvelle note
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'enseignant_id' => 'required|exists:enseignants,id',
            'valeur' => 'required|numeric|min:0|max:20',
            'coefficient' => 'required|numeric|min:0',
            'type_evaluation' => 'required|string|max:50',
            'trimestre' => 'required|integer|min:1|max:3',
            'date_evaluation' => 'required|date',
            'commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Vérifier que l'élève existe
        $eleve = Eleve::find($request->eleve_id);
        if (!$eleve) {
            return response()->json([
                'status' => 'error',
                'message' => 'Élève non trouvé'
            ], 404);
        }
        
        // Vérifier que la matière existe
        $matiere = Matiere::find($request->matiere_id);
        if (!$matiere) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matière non trouvée'
            ], 404);
        }

        $note = new Note();
        $note->eleve_id = $request->eleve_id;
        $note->matiere_id = $request->matiere_id;
        $note->enseignant_id = $request->enseignant_id;
        $note->valeur = $request->valeur;
        $note->coefficient = $request->coefficient;
        $note->type_evaluation = $request->type_evaluation;
        $note->trimestre = $request->trimestre;
        $note->date_evaluation = $request->date_evaluation;
        $note->commentaire = $request->commentaire;
        $note->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'note' => $note->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur'),
            ],
            'message' => 'Note créée avec succès'
        ], 201);
    }

    /**
     * Enregistrer plusieurs notes en une seule requête
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|array|min:1',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.matiere_id' => 'required|exists:matieres,id',
            'notes.*.enseignant_id' => 'required|exists:enseignants,id',
            'notes.*.valeur' => 'required|numeric|min:0|max:20',
            'notes.*.coefficient' => 'required|numeric|min:0',
            'notes.*.type_evaluation' => 'required|string|max:50',
            'notes.*.trimestre' => 'required|integer|min:1|max:3',
            'notes.*.date_evaluation' => 'required|date',
            'notes.*.commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $notesCreees = [];
        
        foreach ($request->notes as $noteData) {
            $note = new Note();
            $note->eleve_id = $noteData['eleve_id'];
            $note->matiere_id = $noteData['matiere_id'];
            $note->enseignant_id = $noteData['enseignant_id'];
            $note->valeur = $noteData['valeur'];
            $note->coefficient = $noteData['coefficient'];
            $note->type_evaluation = $noteData['type_evaluation'];
            $note->trimestre = $noteData['trimestre'];
            $note->date_evaluation = $noteData['date_evaluation'];
            $note->commentaire = $noteData['commentaire'] ?? null;
            $note->save();
            
            $notesCreees[] = $note;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'notes' => $notesCreees,
                'count' => count($notesCreees)
            ],
            'message' => count($notesCreees) . ' notes créées avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'une note spécifique
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
        $note->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur');
        
        return response()->json([
            'status' => 'success',
            'data' => $note,
            'message' => 'Détails de la note récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'une note
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'sometimes|required|exists:eleves,id',
            'matiere_id' => 'sometimes|required|exists:matieres,id',
            'enseignant_id' => 'sometimes|required|exists:enseignants,id',
            'valeur' => 'sometimes|required|numeric|min:0|max:20',
            'coefficient' => 'sometimes|required|numeric|min:0',
            'type_evaluation' => 'sometimes|required|string|max:50',
            'trimestre' => 'sometimes|required|integer|min:1|max:3',
            'date_evaluation' => 'sometimes|required|date',
            'commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        if ($request->has('eleve_id')) {
            $note->eleve_id = $request->eleve_id;
        }
        
        if ($request->has('matiere_id')) {
            $note->matiere_id = $request->matiere_id;
        }
        
        if ($request->has('enseignant_id')) {
            $note->enseignant_id = $request->enseignant_id;
        }
        
        if ($request->has('valeur')) {
            $note->valeur = $request->valeur;
        }
        
        if ($request->has('coefficient')) {
            $note->coefficient = $request->coefficient;
        }
        
        if ($request->has('type_evaluation')) {
            $note->type_evaluation = $request->type_evaluation;
        }
        
        if ($request->has('trimestre')) {
            $note->trimestre = $request->trimestre;
        }
        
        if ($request->has('date_evaluation')) {
            $note->date_evaluation = $request->date_evaluation;
        }
        
        if ($request->has('commentaire')) {
            $note->commentaire = $request->commentaire;
        }
        
        $note->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'note' => $note->fresh()->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur'),
            ],
            'message' => 'Note mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une note
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Note supprimée avec succès'
        ]);
    }
    
    /**
     * Récupérer les notes d'un élève
     *
     * @param  int  $eleveId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notesEleve($eleveId, Request $request)
    {
        $eleve = Eleve::find($eleveId);
        
        if (!$eleve) {
            return response()->json([
                'status' => 'error',
                'message' => 'Élève non trouvé'
            ], 404);
        }
        
        $query = Note::where('eleve_id', $eleveId)
                    ->with('matiere', 'enseignant.utilisateur');
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }
        
        // Filtrage par trimestre
        if ($request->has('trimestre')) {
            $query->where('trimestre', $request->trimestre);
        }
        
        // Filtrage par type d'évaluation
        if ($request->has('type_evaluation')) {
            $query->where('type_evaluation', $request->type_evaluation);
        }
        
        // Tri par date d'évaluation
        $query->orderBy('date_evaluation', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $notes = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $notes,
            'message' => 'Notes de l\'élève récupérées avec succès'
        ]);
    }
    
    /**
     * Récupérer le bulletin de notes d'un élève
     *
     * @param  int  $eleveId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulletinEleve($eleveId, Request $request)
    {
        $eleve = Eleve::with('utilisateur', 'classe')->find($eleveId);
        
        if (!$eleve) {
            return response()->json([
                'status' => 'error',
                'message' => 'Élève non trouvé'
            ], 404);
        }
        
        // Vérifier si le trimestre est spécifié
        $trimestre = $request->input('trimestre', null);
        $anneeScolaire = $request->input('annee_scolaire', null);
        
        $query = Note::where('eleve_id', $eleveId)
                    ->with('matiere', 'enseignant.utilisateur');
        
        if ($trimestre) {
            $query->where('trimestre', $trimestre);
        }
        
        // Récupérer toutes les notes de l'élève
        $notes = $query->get();
        
        // Organiser les notes par matière
        $notesByMatiere = [];
        $moyenneGenerale = 0;
        $totalCoefficients = 0;
        
        foreach ($notes as $note) {
            $matiereId = $note->matiere_id;
            
            if (!isset($notesByMatiere[$matiereId])) {
                $notesByMatiere[$matiereId] = [
                    'matiere' => $note->matiere,
                    'notes' => [],
                    'moyenne' => 0,
                    'coefficient' => $note->matiere->coefficient,
                    'total_points' => 0,
                    'nombre_notes' => 0
                ];
            }
            
            $notesByMatiere[$matiereId]['notes'][] = $note;
            $notesByMatiere[$matiereId]['total_points'] += $note->valeur * $note->coefficient;
            $notesByMatiere[$matiereId]['nombre_notes'] += $note->coefficient;
        }
        
        // Calculer la moyenne par matière et la moyenne générale
        foreach ($notesByMatiere as $matiereId => &$matiereData) {
            if ($matiereData['nombre_notes'] > 0) {
                $matiereData['moyenne'] = $matiereData['total_points'] / $matiereData['nombre_notes'];
                $moyenneGenerale += $matiereData['moyenne'] * $matiereData['coefficient'];
                $totalCoefficients += $matiereData['coefficient'];
            }
        }
        
        if ($totalCoefficients > 0) {
            $moyenneGenerale = $moyenneGenerale / $totalCoefficients;
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'eleve' => $eleve,
                'trimestre' => $trimestre,
                'annee_scolaire' => $anneeScolaire ?? $eleve->classe->annee_scolaire,
                'matieres' => array_values($notesByMatiere),
                'moyenne_generale' => round($moyenneGenerale, 2),
                'appreciation_generale' => $this->getAppreciationFromMoyenne($moyenneGenerale, $eleve->classe)
            ],
            'message' => 'Bulletin de l\'élève récupéré avec succès'
        ]);
    }
    
    /**
     * Obtenir une appréciation en fonction de la moyenne et de la classe
     *
     * @param  float  $moyenne
     * @param  \App\Models\Classe  $classe
     * @return string
     */
    private function getAppreciationFromMoyenne($moyenne, $classe = null)
    {
        if ($classe) {
            $appreciation = $classe->getAppreciation($moyenne);
            return $appreciation['label'];
        }
        
        // Fallback pour les anciens appels sans classe
        if ($moyenne >= 16) {
            return 'Excellent';
        } elseif ($moyenne >= 14) {
            return 'Très bien';
        } elseif ($moyenne >= 12) {
            return 'Bien';
        } elseif ($moyenne >= 10) {
            return 'Assez bien';
        } elseif ($moyenne >= 8) {
            return 'Passable';
        } elseif ($moyenne >= 5) {
            return 'Insuffisant';
        } else {
            return 'Très insuffisant';
        }
    }
}