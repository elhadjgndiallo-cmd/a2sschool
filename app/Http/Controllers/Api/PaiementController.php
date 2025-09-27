<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaiementController extends Controller
{
    /**
     * Afficher la liste des paiements
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Paiement::with('eleve.utilisateur');
        
        // Filtrage par élève
        if ($request->has('eleve_id')) {
            $query->where('eleve_id', $request->eleve_id);
        }
        
        // Filtrage par parent
        if ($request->has('parent_id')) {
            $query->whereHas('eleve.parents', function($q) use ($request) {
                $q->where('parents.id', $request->parent_id);
            });
        }
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtrage par type de paiement
        if ($request->has('type_paiement')) {
            $query->where('type_paiement', $request->type_paiement);
        }
        
        // Filtrage par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }
        
        // Filtrage par montant
        if ($request->has('montant_min') && $request->has('montant_max')) {
            $query->whereBetween('montant', [$request->montant_min, $request->montant_max]);
        } elseif ($request->has('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        } elseif ($request->has('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }
        
        // Tri par date de paiement
        $query->orderBy('date_paiement', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $paiements = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $paiements,
            'message' => 'Liste des paiements récupérée avec succès'
        ]);
    }

    /**
     * Enregistrer un nouveau paiement
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'required|exists:eleves,id',
            'montant' => 'required|numeric|min:0',
            'type_paiement' => 'required|string|max:50',
            'date_paiement' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'statut' => 'required|string|in:en_attente,complete,annule',
            'methode_paiement' => 'required|string|max:50'
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

        $paiement = new Paiement();
        $paiement->eleve_id = $request->eleve_id;
        $paiement->montant = $request->montant;
        $paiement->type_paiement = $request->type_paiement;
        $paiement->date_paiement = $request->date_paiement;
        $paiement->reference = $request->reference;
        $paiement->description = $request->description;
        $paiement->statut = $request->statut;
        $paiement->methode_paiement = $request->methode_paiement;
        $paiement->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'paiement' => $paiement->load('eleve.utilisateur'),
            ],
            'message' => 'Paiement créé avec succès'
        ], 201);
    }

    /**
     * Afficher les détails d'un paiement spécifique
     *
     * @param  \App\Models\Paiement  $paiement
     * @return \Illuminate\Http\Response
     */
    public function show(Paiement $paiement)
    {
        $paiement->load('eleve.utilisateur', 'eleve.classe');
        
        return response()->json([
            'status' => 'success',
            'data' => $paiement,
            'message' => 'Détails du paiement récupérés avec succès'
        ]);
    }

    /**
     * Mettre à jour les informations d'un paiement
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paiement  $paiement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paiement $paiement)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'sometimes|required|exists:eleves,id',
            'montant' => 'sometimes|required|numeric|min:0',
            'type_paiement' => 'sometimes|required|string|max:50',
            'date_paiement' => 'sometimes|required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'statut' => 'sometimes|required|string|in:en_attente,complete,annule',
            'methode_paiement' => 'sometimes|required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        if ($request->has('eleve_id')) {
            $paiement->eleve_id = $request->eleve_id;
        }
        
        if ($request->has('montant')) {
            $paiement->montant = $request->montant;
        }
        
        if ($request->has('type_paiement')) {
            $paiement->type_paiement = $request->type_paiement;
        }
        
        if ($request->has('date_paiement')) {
            $paiement->date_paiement = $request->date_paiement;
        }
        
        if ($request->has('reference')) {
            $paiement->reference = $request->reference;
        }
        
        if ($request->has('description')) {
            $paiement->description = $request->description;
        }
        
        if ($request->has('statut')) {
            $paiement->statut = $request->statut;
        }
        
        if ($request->has('methode_paiement')) {
            $paiement->methode_paiement = $request->methode_paiement;
        }
        
        $paiement->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'paiement' => $paiement->fresh()->load('eleve.utilisateur'),
            ],
            'message' => 'Paiement mis à jour avec succès'
        ]);
    }

    /**
     * Supprimer un paiement
     *
     * @param  \App\Models\Paiement  $paiement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Paiement $paiement)
    {
        $paiement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement supprimé avec succès'
        ]);
    }
    
    /**
     * Changer le statut d'un paiement
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paiement  $paiement
     * @return \Illuminate\Http\Response
     */
    public function changerStatut(Request $request, Paiement $paiement)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|string|in:en_attente,complete,annule',
            'commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $paiement->statut = $request->statut;
        
        if ($request->has('commentaire')) {
            $paiement->description = $request->commentaire;
        }
        
        $paiement->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'paiement' => $paiement->fresh()->load('eleve.utilisateur'),
            ],
            'message' => 'Statut du paiement mis à jour avec succès'
        ]);
    }
    
    /**
     * Récupérer les paiements d'un élève
     *
     * @param  int  $eleveId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paiementsEleve($eleveId, Request $request)
    {
        $eleve = Eleve::find($eleveId);
        
        if (!$eleve) {
            return response()->json([
                'status' => 'error',
                'message' => 'Élève non trouvé'
            ], 404);
        }
        
        $query = Paiement::where('eleve_id', $eleveId);
        
        // Filtrage par type de paiement
        if ($request->has('type_paiement')) {
            $query->where('type_paiement', $request->type_paiement);
        }
        
        // Filtrage par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Filtrage par date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }
        
        // Tri par date de paiement
        $query->orderBy('date_paiement', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $paiements = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $paiements,
            'message' => 'Paiements de l\'élève récupérés avec succès'
        ]);
    }
    
    /**
     * Récupérer les statistiques des paiements
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistiques(Request $request)
    {
        $query = Paiement::query();
        
        // Filtrage par classe
        if ($request->has('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtrage par type de paiement
        if ($request->has('type_paiement')) {
            $query->where('type_paiement', $request->type_paiement);
        }
        
        // Filtrage par période
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        } elseif ($request->has('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        } elseif ($request->has('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }
        
        // Montant total des paiements
        $montantTotal = $query->sum('montant');
        
        // Nombre total de paiements
        $totalPaiements = $query->count();
        
        // Paiements par statut
        $paiementsParStatut = (clone $query)
            ->selectRaw('statut, count(*) as total, sum(montant) as montant_total')
            ->groupBy('statut')
            ->get()
            ->map(function ($item) {
                return [
                    'statut' => $item->statut,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par type
        $paiementsParType = (clone $query)
            ->selectRaw('type_paiement, count(*) as total, sum(montant) as montant_total')
            ->groupBy('type_paiement')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type_paiement,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par méthode
        $paiementsParMethode = (clone $query)
            ->selectRaw('methode_paiement, count(*) as total, sum(montant) as montant_total')
            ->groupBy('methode_paiement')
            ->get()
            ->map(function ($item) {
                return [
                    'methode' => $item->methode_paiement,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par mois (pour l'année en cours)
        $paiementsParMois = (clone $query)
            ->whereYear('date_paiement', date('Y'))
            ->selectRaw('MONTH(date_paiement) as mois, count(*) as total, sum(montant) as montant_total')
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'montant_total' => $montantTotal,
                'total_paiements' => $totalPaiements,
                'paiements_par_statut' => $paiementsParStatut,
                'paiements_par_type' => $paiementsParType,
                'paiements_par_methode' => $paiementsParMethode,
                'paiements_par_mois' => $paiementsParMois
            ],
            'message' => 'Statistiques des paiements récupérées avec succès'
        ]);
    }
}