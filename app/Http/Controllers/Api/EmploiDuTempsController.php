<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploiDuTemps;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmploiDuTempsController extends Controller
{
    /**
     * Afficher la liste des emplois du temps
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EmploiDuTemps::with(['classe', 'enseignant.utilisateur', 'matiere']);
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }
        
        // Filtrage par enseignant
        if ($request->has('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }
        
        // Filtrage par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }
        
        // Filtrage par jour
        if ($request->has('jour')) {
            $query->where('jour', $request->jour);
        }
        
        // Filtrage par heure de début
        if ($request->has('heure_debut')) {
            $query->where('heure_debut', $request->heure_debut);
        }
        
        // Filtrage par heure de fin
        if ($request->has('heure_fin')) {
            $query->where('heure_fin', $request->heure_fin);
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'jour');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        // Tri secondaire par heure de début
        if ($sortField != 'heure_debut') {
            $query->orderBy('heure_debut', 'asc');
        }
        
        // Pagination
        $perPage = $request->input('per_page', 50);
        $emploisDuTemps = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $emploisDuTemps,
            'message' => 'Liste des emplois du temps récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouveau créneau d'emploi du temps
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classe_id' => 'required|exists:classes,id',
            'enseignant_id' => 'required|exists:enseignants,id',
            'matiere_id' => 'required|exists:matieres,id',
            'jour' => 'required|integer|between:1,7',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'salle' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            // Vérifier si l'enseignant est disponible sur ce créneau
            $conflitEnseignant = $this->verifierDisponibiliteEnseignant(
                $request->enseignant_id,
                $request->jour,
                $request->heure_debut,
                $request->heure_fin
            );
            
            if ($conflitEnseignant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'L\'enseignant a déjà un cours programmé sur ce créneau horaire'
                ], 422);
            }
            
            // Vérifier si la classe est disponible sur ce créneau
            $conflitClasse = $this->verifierDisponibiliteClasse(
                $request->classe_id,
                $request->jour,
                $request->heure_debut,
                $request->heure_fin
            );
            
            if ($conflitClasse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La classe a déjà un cours programmé sur ce créneau horaire'
                ], 422);
            }
            
            // Vérifier si l'enseignant enseigne bien cette matière
            $enseigneMatiere = DB::table('enseignant_matiere')
                ->where('enseignant_id', $request->enseignant_id)
                ->where('matiere_id', $request->matiere_id)
                ->exists();
            
            if (!$enseigneMatiere) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cet enseignant n\'enseigne pas cette matière'
                ], 422);
            }
            
            // Créer le créneau d'emploi du temps
            $emploiDuTemps = EmploiDuTemps::create($request->all());
            
            return response()->json([
                'status' => 'success',
                'data' => $emploiDuTemps->load(['classe', 'enseignant.utilisateur', 'matiere']),
                'message' => 'Créneau d\'emploi du temps créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création du créneau d\'emploi du temps: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrer plusieurs créneaux d'emploi du temps en une seule fois
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'creneaux' => 'required|array|min:1',
            'creneaux.*.classe_id' => 'required|exists:classes,id',
            'creneaux.*.enseignant_id' => 'required|exists:enseignants,id',
            'creneaux.*.matiere_id' => 'required|exists:matieres,id',
            'creneaux.*.jour' => 'required|integer|between:1,7',
            'creneaux.*.heure_debut' => 'required|date_format:H:i',
            'creneaux.*.heure_fin' => 'required|date_format:H:i|after:creneaux.*.heure_debut',
            'creneaux.*.salle' => 'nullable|string|max:50',
            'creneaux.*.description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $creneauxCrees = [];
            $erreurs = [];
            
            foreach ($request->creneaux as $index => $creneau) {
                // Vérifier si l'enseignant est disponible sur ce créneau
                $conflitEnseignant = $this->verifierDisponibiliteEnseignant(
                    $creneau['enseignant_id'],
                    $creneau['jour'],
                    $creneau['heure_debut'],
                    $creneau['heure_fin']
                );
                
                // Vérifier si la classe est disponible sur ce créneau
                $conflitClasse = $this->verifierDisponibiliteClasse(
                    $creneau['classe_id'],
                    $creneau['jour'],
                    $creneau['heure_debut'],
                    $creneau['heure_fin']
                );
                
                // Vérifier si l'enseignant enseigne bien cette matière
                $enseigneMatiere = DB::table('enseignant_matiere')
                    ->where('enseignant_id', $creneau['enseignant_id'])
                    ->where('matiere_id', $creneau['matiere_id'])
                    ->exists();
                
                if ($conflitEnseignant) {
                    $erreurs[] = [
                        'index' => $index,
                        'message' => 'L\'enseignant a déjà un cours programmé sur ce créneau horaire'
                    ];
                    continue;
                }
                
                if ($conflitClasse) {
                    $erreurs[] = [
                        'index' => $index,
                        'message' => 'La classe a déjà un cours programmé sur ce créneau horaire'
                    ];
                    continue;
                }
                
                if (!$enseigneMatiere) {
                    $erreurs[] = [
                        'index' => $index,
                        'message' => 'Cet enseignant n\'enseigne pas cette matière'
                    ];
                    continue;
                }
                
                // Créer le créneau d'emploi du temps
                $emploiDuTemps = EmploiDuTemps::create($creneau);
                $creneauxCrees[] = $emploiDuTemps;
            }
            
            if (empty($creneauxCrees) && !empty($erreurs)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'errors' => $erreurs,
                    'message' => 'Aucun créneau n\'a pu être créé en raison des erreurs'
                ], 422);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'creneaux_crees' => $creneauxCrees,
                    'erreurs' => $erreurs,
                    'total_crees' => count($creneauxCrees),
                    'total_erreurs' => count($erreurs)
                ],
                'message' => count($erreurs) > 0 
                    ? 'Certains créneaux ont été créés avec des erreurs' 
                    : 'Tous les créneaux ont été créés avec succès'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création des créneaux d\'emploi du temps: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un créneau d'emploi du temps spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $emploiDuTemps = EmploiDuTemps::with(['classe', 'enseignant.utilisateur', 'matiere'])
                ->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $emploiDuTemps,
                'message' => 'Détails du créneau d\'emploi du temps récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Créneau d\'emploi du temps non trouvé'
            ], 404);
        }
    }

    /**
     * Mettre à jour un créneau d'emploi du temps existant
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'classe_id' => 'sometimes|required|exists:classes,id',
            'enseignant_id' => 'sometimes|required|exists:enseignants,id',
            'matiere_id' => 'sometimes|required|exists:matieres,id',
            'jour' => 'sometimes|required|integer|between:1,7',
            'heure_debut' => 'sometimes|required|date_format:H:i',
            'heure_fin' => 'sometimes|required|date_format:H:i|after:heure_debut',
            'salle' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $emploiDuTemps = EmploiDuTemps::findOrFail($id);
            
            // Préparer les données pour les vérifications
            $enseignantId = $request->input('enseignant_id', $emploiDuTemps->enseignant_id);
            $classeId = $request->input('classe_id', $emploiDuTemps->classe_id);
            $matiereId = $request->input('matiere_id', $emploiDuTemps->matiere_id);
            $jour = $request->input('jour', $emploiDuTemps->jour);
            $heureDebut = $request->input('heure_debut', $emploiDuTemps->heure_debut);
            $heureFin = $request->input('heure_fin', $emploiDuTemps->heure_fin);
            
            // Vérifier si l'enseignant est disponible sur ce créneau (en excluant le créneau actuel)
            $conflitEnseignant = $this->verifierDisponibiliteEnseignant(
                $enseignantId,
                $jour,
                $heureDebut,
                $heureFin,
                $id
            );
            
            if ($conflitEnseignant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'L\'enseignant a déjà un cours programmé sur ce créneau horaire'
                ], 422);
            }
            
            // Vérifier si la classe est disponible sur ce créneau (en excluant le créneau actuel)
            $conflitClasse = $this->verifierDisponibiliteClasse(
                $classeId,
                $jour,
                $heureDebut,
                $heureFin,
                $id
            );
            
            if ($conflitClasse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La classe a déjà un cours programmé sur ce créneau horaire'
                ], 422);
            }
            
            // Vérifier si l'enseignant enseigne bien cette matière
            if ($request->has('enseignant_id') || $request->has('matiere_id')) {
                $enseigneMatiere = DB::table('enseignant_matiere')
                    ->where('enseignant_id', $enseignantId)
                    ->where('matiere_id', $matiereId)
                    ->exists();
                
                if (!$enseigneMatiere) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cet enseignant n\'enseigne pas cette matière'
                    ], 422);
                }
            }
            
            // Mettre à jour le créneau d'emploi du temps
            $emploiDuTemps->update($request->all());
            
            return response()->json([
                'status' => 'success',
                'data' => $emploiDuTemps->fresh(['classe', 'enseignant.utilisateur', 'matiere']),
                'message' => 'Créneau d\'emploi du temps mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du créneau d\'emploi du temps: ' . $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Supprimer un créneau d'emploi du temps
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $emploiDuTemps = EmploiDuTemps::findOrFail($id);
            $emploiDuTemps->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Créneau d\'emploi du temps supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du créneau d\'emploi du temps: ' . $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Récupérer l'emploi du temps d'une classe spécifique
     *
     * @param  int  $classeId
     * @return \Illuminate\Http\Response
     */
    public function emploiDuTempsClasse($classeId)
    {
        try {
            // Vérifier si la classe existe
            $classe = Classe::findOrFail($classeId);
            
            // Récupérer l'emploi du temps de la classe
            $emploiDuTemps = EmploiDuTemps::with(['enseignant.utilisateur', 'matiere'])
                ->where('classe_id', $classeId)
                ->orderBy('jour')
                ->orderBy('heure_debut')
                ->get();
            
            // Organiser l'emploi du temps par jour
            $emploiParJour = [];
            $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            
            foreach ($joursSemaine as $index => $jour) {
                $jourId = $index + 1;
                $emploiParJour[$jour] = $emploiDuTemps->filter(function ($creneau) use ($jourId) {
                    return $creneau->jour == $jourId;
                })->values();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'classe' => $classe,
                    'emploi_du_temps' => $emploiParJour
                ],
                'message' => 'Emploi du temps de la classe récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Classe non trouvée ou erreur lors de la récupération de l\'emploi du temps'
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Récupérer l'emploi du temps d'un enseignant spécifique
     *
     * @param  int  $enseignantId
     * @return \Illuminate\Http\Response
     */
    public function emploiDuTempsEnseignant($enseignantId)
    {
        try {
            // Vérifier si l'enseignant existe
            $enseignant = Enseignant::with('utilisateur')->findOrFail($enseignantId);
            
            // Récupérer l'emploi du temps de l'enseignant
            $emploiDuTemps = EmploiDuTemps::with(['classe', 'matiere'])
                ->where('enseignant_id', $enseignantId)
                ->orderBy('jour')
                ->orderBy('heure_debut')
                ->get();
            
            // Organiser l'emploi du temps par jour
            $emploiParJour = [];
            $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            
            foreach ($joursSemaine as $index => $jour) {
                $jourId = $index + 1;
                $emploiParJour[$jour] = $emploiDuTemps->filter(function ($creneau) use ($jourId) {
                    return $creneau->jour == $jourId;
                })->values();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'enseignant' => $enseignant,
                    'emploi_du_temps' => $emploiParJour
                ],
                'message' => 'Emploi du temps de l\'enseignant récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Enseignant non trouvé ou erreur lors de la récupération de l\'emploi du temps'
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Vérifier la disponibilité d'un enseignant sur un créneau horaire
     *
     * @param  int  $enseignantId
     * @param  int  $jour
     * @param  string  $heureDebut
     * @param  string  $heureFin
     * @param  int|null  $excludeId
     * @return bool
     */
    private function verifierDisponibiliteEnseignant($enseignantId, $jour, $heureDebut, $heureFin, $excludeId = null)
    {
        $query = EmploiDuTemps::where('enseignant_id', $enseignantId)
            ->where('jour', $jour)
            ->where(function ($query) use ($heureDebut, $heureFin) {
                // Vérifie si le nouveau créneau chevauche un créneau existant
                $query->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '>=', $heureDebut)
                      ->where('heure_debut', '<', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_fin', '>', $heureDebut)
                      ->where('heure_fin', '<=', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<=', $heureDebut)
                      ->where('heure_fin', '>=', $heureFin);
                });
            });
        
        // Exclure le créneau actuel en cas de mise à jour
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Vérifier la disponibilité d'une classe sur un créneau horaire
     *
     * @param  int  $classeId
     * @param  int  $jour
     * @param  string  $heureDebut
     * @param  string  $heureFin
     * @param  int|null  $excludeId
     * @return bool
     */
    private function verifierDisponibiliteClasse($classeId, $jour, $heureDebut, $heureFin, $excludeId = null)
    {
        $query = EmploiDuTemps::where('classe_id', $classeId)
            ->where('jour', $jour)
            ->where(function ($query) use ($heureDebut, $heureFin) {
                // Vérifie si le nouveau créneau chevauche un créneau existant
                $query->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '>=', $heureDebut)
                      ->where('heure_debut', '<', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_fin', '>', $heureDebut)
                      ->where('heure_fin', '<=', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<=', $heureDebut)
                      ->where('heure_fin', '>=', $heureFin);
                });
            });
        
        // Exclure le créneau actuel en cas de mise à jour
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}