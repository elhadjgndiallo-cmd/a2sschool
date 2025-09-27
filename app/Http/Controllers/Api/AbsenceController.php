<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsenceController extends Controller
{
    /**
     * Afficher la liste des absences
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Absence::with('eleve.utilisateur', 'matiere', 'enseignant.utilisateur');
        
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
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_absence', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_absence', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_absence', '<=', $request->date_fin);
        }
        
        // Filtrage par justification
        if ($request->has('justifiee')) {
            $query->where('justifiee', $request->justifiee);
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $absences = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $absences,
            'message' => 'Liste des absences récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer une nouvelle absence
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
            'date_absence' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'justifiee' => 'required|boolean',
            'motif' => 'nullable|string',
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

        $absence = new Absence();
        $absence->eleve_id = $request->eleve_id;
        $absence->matiere_id = $request->matiere_id;
        $absence->enseignant_id = $request->enseignant_id;
        $absence->date_absence = $request->date_absence;
        $absence->heure_debut = $request->heure_debut;
        $absence->heure_fin = $request->heure_fin;
        $absence->justifiee = $request->justifiee;
        $absence->motif = $request->motif;
        $absence->commentaire = $request->commentaire;
        $absence->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'absence' => $absence->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur'),
            ],
            'message' => 'Absence créée avec succès'
        ], 201);
    }

    /**
     * Enregistrer plusieurs absences en une seule requête
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'absences' => 'required|array|min:1',
            'absences.*.eleve_id' => 'required|exists:eleves,id',
            'absences.*.matiere_id' => 'required|exists:matieres,id',
            'absences.*.enseignant_id' => 'required|exists:enseignants,id',
            'absences.*.date_absence' => 'required|date',
            'absences.*.heure_debut' => 'required|date_format:H:i',
            'absences.*.heure_fin' => 'required|date_format:H:i',
            'absences.*.justifiee' => 'required|boolean',
            'absences.*.motif' => 'nullable|string',
            'absences.*.commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $absencesCreees = [];
        
        foreach ($request->absences as $absenceData) {
            $absence = new Absence();
            $absence->eleve_id = $absenceData['eleve_id'];
            $absence->matiere_id = $absenceData['matiere_id'];
            $absence->enseignant_id = $absenceData['enseignant_id'];
            $absence->date_absence = $absenceData['date_absence'];
            $absence->heure_debut = $absenceData['heure_debut'];
            $absence->heure_fin = $absenceData['heure_fin'];
            $absence->justifiee = $absenceData['justifiee'];
            $absence->motif = $absenceData['motif'] ?? null;
            $absence->commentaire = $absenceData['commentaire'] ?? null;
            $absence->save();
            
            $absencesCreees[] = $absence;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'absences' => $absencesCreees,
                'count' => count($absencesCreees)
            ],
            'message' => count($absencesCreees) . ' absences créées avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'une absence spécifique
     *
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function show(Absence $absence)
    {
        $absence->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur');
        
        return response()->json([
            'status' => 'success',
            'data' => $absence,
            'message' => 'Détails de l\'absence récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'une absence
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Absence $absence)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'sometimes|required|exists:eleves,id',
            'matiere_id' => 'sometimes|required|exists:matieres,id',
            'enseignant_id' => 'sometimes|required|exists:enseignants,id',
            'date_absence' => 'sometimes|required|date',
            'heure_debut' => 'sometimes|required|date_format:H:i',
            'heure_fin' => 'sometimes|required|date_format:H:i|after:heure_debut',
            'justifiee' => 'sometimes|required|boolean',
            'motif' => 'nullable|string',
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
            $absence->eleve_id = $request->eleve_id;
        }
        
        if ($request->has('matiere_id')) {
            $absence->matiere_id = $request->matiere_id;
        }
        
        if ($request->has('enseignant_id')) {
            $absence->enseignant_id = $request->enseignant_id;
        }
        
        if ($request->has('date_absence')) {
            $absence->date_absence = $request->date_absence;
        }
        
        if ($request->has('heure_debut')) {
            $absence->heure_debut = $request->heure_debut;
        }
        
        if ($request->has('heure_fin')) {
            $absence->heure_fin = $request->heure_fin;
        }
        
        if ($request->has('justifiee')) {
            $absence->justifiee = $request->justifiee;
        }
        
        if ($request->has('motif')) {
            $absence->motif = $request->motif;
        }
        
        if ($request->has('commentaire')) {
            $absence->commentaire = $request->commentaire;
        }
        
        $absence->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'absence' => $absence->fresh()->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur'),
            ],
            'message' => 'Absence mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une absence
     *
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Absence $absence)
    {
        $absence->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Absence supprimée avec succès'
        ]);
    }
    
    /**
     * Justifier une absence
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function justifier(Request $request, Absence $absence)
    {
        $validator = Validator::make($request->all(), [
            'motif' => 'required|string',
            'commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $absence->justifiee = true;
        $absence->motif = $request->motif;
        
        if ($request->has('commentaire')) {
            $absence->commentaire = $request->commentaire;
        }
        
        $absence->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'absence' => $absence->fresh()->load('eleve.utilisateur', 'matiere', 'enseignant.utilisateur'),
            ],
            'message' => 'Absence justifiée avec succès'
        ]);
    }
    
    /**
     * Récupérer les absences d'un élève
     *
     * @param  int  $eleveId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function absencesEleve($eleveId, Request $request)
    {
        $eleve = Eleve::find($eleveId);
        
        if (!$eleve) {
            return response()->json([
                'status' => 'error',
                'message' => 'Élève non trouvé'
            ], 404);
        }
        
        $query = Absence::where('eleve_id', $eleveId)
                    ->with('matiere', 'enseignant.utilisateur');
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_absence', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_absence', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_absence', '<=', $request->date_fin);
        }
        
        // Filtrage par justification
        if ($request->has('justifiee')) {
            $query->where('justifiee', $request->justifiee);
        }
        
        // Tri par date d'absence
        $query->orderBy('date_absence', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $absences = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $absences,
            'message' => 'Absences de l\'élève récupérées avec succès'
        ]);
    }
    
    /**
     * Récupérer les statistiques d'absences
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistiques(Request $request)
    {
        $query = Absence::query();
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }
        
        // Filtrage par période
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_absence', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_absence', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_absence', '<=', $request->date_fin);
        }
        
        // Nombre total d'absences
        $totalAbsences = $query->count();
        
        // Nombre d'absences justifiées
        $absencesJustifiees = (clone $query)->where('justifiee', true)->count();
        
        // Nombre d'absences non justifiées
        $absencesNonJustifiees = $totalAbsences - $absencesJustifiees;
        
        // Taux de justification
        $tauxJustification = $totalAbsences > 0 ? ($absencesJustifiees / $totalAbsences) * 100 : 0;
        
        // Absences par matière
        $absencesParMatiere = (clone $query)
            ->selectRaw('matiere_id, count(*) as total')
            ->groupBy('matiere_id')
            ->with('matiere')
            ->get()
            ->map(function ($item) {
                return [
                    'matiere' => $item->matiere->nom,
                    'total' => $item->total
                ];
            });
        
        // Absences par élève (top 10)
        $absencesParEleve = (clone $query)
            ->selectRaw('eleve_id, count(*) as total')
            ->groupBy('eleve_id')
            ->with('eleve.utilisateur')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'eleve' => $item->eleve->utilisateur->nom . ' ' . $item->eleve->utilisateur->prenom,
                    'total' => $item->total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_absences' => $totalAbsences,
                'absences_justifiees' => $absencesJustifiees,
                'absences_non_justifiees' => $absencesNonJustifiees,
                'taux_justification' => round($tauxJustification, 2),
                'absences_par_matiere' => $absencesParMatiere,
                'absences_par_eleve' => $absencesParEleve
            ],
            'message' => 'Statistiques des absences récupérées avec succès'
        ]);
    }
}