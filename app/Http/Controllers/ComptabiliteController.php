<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entree;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\SalaireEnseignant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ComptabiliteEntreesStatsService;
use App\Services\ComptabiliteSortiesStatsService;
use App\Services\FacturationService;
use Illuminate\Support\Facades\Cache;

class ComptabiliteController extends Controller
{
    /**
     * Durée du cache pour le dashboard comptabilité (3 minutes)
     */
    const CACHE_DURATION = 180;
    
    /**
     * Afficher le tableau de bord de la comptabilité
     */
    public function index()
    {
        try {
            // Récupérer l'année scolaire active pour filtrer les données
            $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
            
            if (!$anneeScolaireActive) {
                return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
            }
            
            // Statistiques générales pour l'année active (avec cache)
            $stats = Cache::remember(
                'comptabilite_stats_' . $anneeScolaireActive->id, 
                self::CACHE_DURATION, 
                fn() => $this->getComptabiliteStats($anneeScolaireActive)
            );
        
        $entreesStats = app(ComptabiliteEntreesStatsService::class);

        $toutesLesEntrees = $entreesStats->buildListEntries(new Request(), $anneeScolaireActive)
            ->take(10);

        $sortiesStatsService = app(ComptabiliteSortiesStatsService::class);
        $toutesLesSorties = $sortiesStatsService->buildListEntries(new Request(), $anneeScolaireActive)
            ->take(10);
        
        // Calculer les totaux RÉELS (pas seulement les 10 derniers) pour les statistiques
        $totalRevenus = $stats['revenus_total'];
        $totalSorties = $stats['depenses_total'];
        $beneficeTotal = $stats['benefice_total'];
        
        // Générer les données pour le graphique d'évolution (6 derniers mois)
        $evolutionData = $this->getEvolutionData($anneeScolaireActive);
        
        return view('comptabilite.index', compact('stats', 'toutesLesEntrees', 'toutesLesSorties', 'anneeScolaireActive', 'totalRevenus', 'totalSorties', 'beneficeTotal', 'evolutionData'));
        
        } catch (\Exception $e) {
            \Log::error('Erreur dans comptabilite.index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la comptabilité: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtenir les données d'évolution pour le graphique (6 derniers mois)
     * OPTIMISÉ avec cache
     */
    private function getEvolutionData($anneeScolaire)
    {
        // Cache pour 10 minutes
        return Cache::remember(
            'comptabilite_evolution_' . $anneeScolaire->id, 
            600, 
            function() use ($anneeScolaire) {
                $mois = [];
                $revenus = [];
                $depenses = [];
                
                // Obtenir les 6 derniers mois
                for ($i = 5; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $moisDebut = $date->copy()->startOfMonth();
                    $moisFin = $date->copy()->endOfMonth();
                    
                    // Vérifier que le mois est dans l'année scolaire
                    if ($moisFin->lt($anneeScolaire->date_debut) || $moisDebut->gt($anneeScolaire->date_fin)) {
                        continue;
                    }
                    
                    // Nom du mois en français
                    $nomMois = $date->locale('fr')->isoFormat('MMM YYYY');
                    $mois[] = $nomMois;
                    
                    // OPTIMISÉ: Requêtes directes au lieu du service lourd
                    $statsMois = app(ComptabiliteEntreesStatsService::class)->calculateStats(
                        new Request([
                            'date_debut' => $moisDebut->format('Y-m-d'),
                            'date_fin' => $moisFin->format('Y-m-d'),
                        ]),
                        $anneeScolaire
                    );
                    $revenus[] = $statsMois['total'];
                    
                    // OPTIMISÉ: Requête unique pour dépenses + salaires
                    $depensesNormales = Depense::whereBetween('date_depense', [
                        $moisDebut->format('Y-m-d'),
                        $moisFin->format('Y-m-d')
                    ])->sum('montant');
                    
                    $salaires = SalaireEnseignant::where('statut', 'payé')
                        ->whereNotNull('date_paiement')
                        ->whereBetween('date_paiement', [
                            $moisDebut->format('Y-m-d'),
                            $moisFin->format('Y-m-d')
                        ])
                        ->sum('salaire_net');
                    
                    $depenses[] = $depensesNormales + $salaires;
                }
                
                return [
                    'labels' => $mois,
                    'revenus' => $revenus,
                    'depenses' => $depenses
                ];
            }
        );
    }

    /**
     * Afficher les rapports de comptabilité
     */
    public function rapports(Request $request)
    {
        // Récupérer l'année scolaire (filtrée ou active par défaut) - comme dans entrees
        $anneeScolaireId = $request->filled('annee_scolaire_id') 
            ? $request->annee_scolaire_id 
            : (\App\Models\AnneeScolaire::anneeActive()?->id);
        
        $anneeScolaire = $anneeScolaireId 
            ? \App\Models\AnneeScolaire::find($anneeScolaireId)
            : \App\Models\AnneeScolaire::anneeActive();
        
        // Utiliser l'année scolaire sélectionnée par défaut (comme dans entrees)
        // Les dates par défaut sont celles de l'année scolaire (comme dans entrees et sorties)
        if ($anneeScolaire) {
            // Utiliser les dates de l'année scolaire par défaut (comme dans entrees)
            $dateDebut = $request->filled('date_debut') 
                ? Carbon::parse($request->date_debut) 
                : Carbon::parse($anneeScolaire->date_debut);
            
            $dateFin = $request->filled('date_fin') 
                ? Carbon::parse($request->date_fin) 
                : Carbon::parse($anneeScolaire->date_fin);
        } else {
            // Si pas d'année scolaire, utiliser le mois actuel
            $dateDebut = $request->filled('date_debut') 
                ? Carbon::parse($request->date_debut) 
                : Carbon::now()->startOfMonth();
            
            $dateFin = $request->filled('date_fin') 
                ? Carbon::parse($request->date_fin) 
                : Carbon::now()->endOfMonth();
        }
        
        // Rapports financiers (passer l'année scolaire et la requête pour filtrer correctement)
        $rapports = $this->genererRapports($dateDebut, $dateFin, $anneeScolaire, $request);
        
        // Évolution des revenus par mois
        $evolutionRevenus = $this->getEvolutionRevenus();
        
        // Évolution des dépenses par mois
        $evolutionDepenses = $this->getEvolutionDepenses();
        
        // Top 5 des classes par revenus
        $topClasses = $this->getTopClassesRevenus($dateDebut, $dateFin);
        
        return view('comptabilite.rapports', compact(
            'rapports', 
            'evolutionRevenus', 
            'evolutionDepenses', 
            'topClasses',
            'dateDebut',
            'dateFin',
            'anneeScolaire'
        ));
    }

    /**
     * Afficher les entrées (revenus)
     */
    public function entrees(Request $request)
    {
        $anneeScolaireId = $request->filled('annee_scolaire_id')
            ? $request->annee_scolaire_id
            : (\App\Models\AnneeScolaire::anneeActive()?->id);

        $anneeScolaire = $anneeScolaireId
            ? \App\Models\AnneeScolaire::find($anneeScolaireId)
            : \App\Models\AnneeScolaire::anneeActive();

        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire trouvée. Veuillez sélectionner une année scolaire.');
        }

        $entreesStats = app(ComptabiliteEntreesStatsService::class);
        $listRequest = $this->entreesListRequest($request, $anneeScolaire);

        $allEntries = $entreesStats->buildListEntries($listRequest, $anneeScolaire);

        $statsEntrees = $entreesStats->statsFromEntries($allEntries);

        $perPage = 50;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allEntries->slice($offset, $perPage);

        $paginatedEntries = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allEntries->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        $paginatedEntries->appends(request()->query());

        $sources = $this->getEntreesSourcesForFilter($anneeScolaire, $entreesStats);
        $anneesScolaires = \App\Models\AnneeScolaire::orderBy('date_debut', 'desc')->get();

        return view('comptabilite.entrees', compact('paginatedEntries', 'statsEntrees', 'sources', 'anneeScolaire', 'anneesScolaires'));
    }

    /**
     * Requête pour la liste entrées (période étendue si année active).
     */
    private function entreesListRequest(Request $request, \App\Models\AnneeScolaire $anneeScolaire): Request
    {
        return $request;
    }

    /**
     * Sources disponibles pour le filtre entrées (requête légère).
     */
    private function getEntreesSourcesForFilter(\App\Models\AnneeScolaire $anneeScolaire, ComptabiliteEntreesStatsService $entreesStats): \Illuminate\Support\Collection
    {
        $typesFrais = DB::table('paiements')
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->join('eleves', 'frais_scolarite.eleve_id', '=', 'eleves.id')
            ->where('eleves.annee_scolaire_id', $anneeScolaire->id)
            ->whereNotNull('frais_scolarite.type_frais')
            ->distinct()
            ->pluck('frais_scolarite.type_frais');

        $sources = $typesFrais->map(fn ($type) => $entreesStats->sourceFromTypeFrais($type))->unique()->values()->all();

        $sourcesEntrees = Entree::query()
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->whereNotIn('source', ComptabiliteEntreesStatsService::SOURCES_SCOLARITE)
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source')
            ->toArray();

        $sources = array_unique(array_merge($sources, $sourcesEntrees));

        if (\App\Models\Facture::whereIn('statut', \App\Models\Facture::statutsActifs())->where('annee_scolaire_id', $anneeScolaire->id)->exists()) {
            $sources[] = 'Frais de scolarité';
        }

        sort($sources);

        return collect($sources);
    }

    /**
     * Afficher les sorties (dépenses)
     */
    public function sorties(Request $request)
    {
        // Année scolaire sélectionnée ou active par défaut (comme comptabilite/entrees)
        $anneeScolaireId = $request->filled('annee_scolaire_id')
            ? $request->annee_scolaire_id
            : (\App\Models\AnneeScolaire::anneeActive()?->id);

        $anneeScolaire = $anneeScolaireId
            ? \App\Models\AnneeScolaire::find($anneeScolaireId)
            : \App\Models\AnneeScolaire::anneeActive();

        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire trouvée. Veuillez sélectionner une année scolaire.');
        }

        $sortiesStatsService = app(ComptabiliteSortiesStatsService::class);
        $allSorties = $sortiesStatsService->buildListEntries($request, $anneeScolaire);

        $statsSorties = $sortiesStatsService->statsFromEntries($allSorties);

        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allSorties->slice($offset, $perPage)->values();

        $sorties = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allSorties->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        $sorties->appends(request()->query());

        $periodeSorties = $sortiesStatsService->effectiveSchoolYearDateRange($anneeScolaire);

        $typesDepense = Depense::query()
            ->whereBetween('date_depense', [$periodeSorties['debut'], $periodeSorties['fin']])
            ->where('type_depense', '!=', 'salaire_enseignant')
            ->select('type_depense')
            ->distinct()
            ->orderBy('type_depense')
            ->pluck('type_depense')
            ->toArray();

        $salairesDansAnnee = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [$periodeSorties['debut'], $periodeSorties['fin']])
            ->exists();

        $depensesSalaireOrphelines = Depense::query()
            ->where('type_depense', 'salaire_enseignant')
            ->where('statut', '!=', 'annule')
            ->whereBetween('date_depense', [$periodeSorties['debut'], $periodeSorties['fin']])
            ->exists();

        if (($salairesDansAnnee || $depensesSalaireOrphelines) && !in_array('salaire_enseignant', $typesDepense, true)) {
            $typesDepense[] = 'salaire_enseignant';
            sort($typesDepense);
        }

        $typesDepense = collect($typesDepense);
        $anneesScolaires = \App\Models\AnneeScolaire::orderBy('date_debut', 'desc')->get();

        return view('comptabilite.sorties', compact(
            'sorties',
            'statsSorties',
            'anneeScolaire',
            'anneesScolaires',
            'typesDepense'
        ));
    }

    /**
     * Obtenir les statistiques générales de la comptabilité
     */
    private function getComptabiliteStats($anneeScolaireActive = null)
    {
        $moisActuel = Carbon::now();
        
        // NOUVEAU: Utiliser la même logique que getStatsEntrees pour le total
        // pour garantir la cohérence entre le dashboard et la page entrées
        $revenusTotal = 0;
        $depensesTotal = 0;
        
        if ($anneeScolaireActive) {
            // Calcul identique à getStatsEntrees
            $request = new Request(); // Request vide pour pas de filtres
            $statsEntrees = $this->getStatsEntrees($request, $anneeScolaireActive);
            $revenusTotal = $statsEntrees['total']; // Changé de 'total_entrees' à 'total'
            
            $depensesTotal = app(ComptabiliteSortiesStatsService::class)
                ->calculateStats($request, $anneeScolaireActive)['total'];
        }
        
        // Bénéfice total
        $beneficeTotal = $revenusTotal - $depensesTotal;
        
        // Nombre d'élèves avec paiements en attente pour l'année active
        $elevesEnAttente = 0;
        if ($anneeScolaireActive) {
            $elevesEnAttente = FraisScolarite::whereHas('eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->where('statut', 'en_attente')
            ->distinct('eleve_id')
            ->count();
        }
            
        return [
            'revenus_total' => $revenusTotal,
            'depenses_total' => $depensesTotal,
            'benefice_total' => $beneficeTotal,
            'eleves_en_attente' => $elevesEnAttente
        ];
    }

    /**
     * Générer les rapports financiers
     */
    private function genererRapports($dateDebut, $dateFin, $anneeScolaire = null, $request = null)
    {
        // Utiliser l'année scolaire fournie ou l'année active par défaut
        if (!$anneeScolaire) {
            $anneeScolaire = \App\Models\AnneeScolaire::anneeActive();
        }
        
        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Récupérer les paiements filtrés par année scolaire ET par dates (comme dans entrees)
        // Filtrer d'abord par année scolaire (comme dans entrees)
        $paiementsQuery = Paiement::query();
        
        if ($anneeScolaire) {
            $paiementsQuery->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
            });
        }
        
        // Ensuite appliquer les filtres de date seulement si les dates sont différentes de l'année scolaire (comme dans entrees)
        // Si les dates correspondent à l'année scolaire, on ne filtre pas par date supplémentaire
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $paiementsQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $paiementsQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
        } else {
            // Si pas de filtres de date supplémentaires, utiliser les dates de l'année scolaire
            $paiementsQuery->whereBetween('date_paiement', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        $paiementsReferences = $paiementsQuery->pluck('reference_paiement')->filter()->toArray();
        
        // Revenus par source (entrées manuelles uniquement, exclure les sources automatiques)
        // Filtrer d'abord par année scolaire (comme dans entrees)
        $revenusParTypeQuery = Entree::query();
        
        if ($anneeScolaire) {
            $revenusParTypeQuery->whereBetween('date_entree', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Ensuite appliquer les filtres de date supplémentaires seulement si fournis (comme dans entrees)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $revenusParTypeQuery->whereDate('date_entree', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $revenusParTypeQuery->whereDate('date_entree', '<=', $request->date_fin);
            }
        }
        
        // Exclure les sources automatiques
        $revenusParTypeQuery->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $revenusParTypeQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $revenusParType = $revenusParTypeQuery->select('source', DB::raw('SUM(montant) as total'))
            ->groupBy('source')
            ->get();
        
        // Ajouter les paiements par type de frais (filtrés par année scolaire et dates)
        // Filtrer d'abord par année scolaire (comme dans entrees)
        $paiementsParTypeFraisQuery = Paiement::select('frais_scolarite.type_frais', DB::raw('SUM(paiements.montant_paye) as total'))
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->whereNotNull('frais_scolarite.type_frais');
        
        // Filtrer les paiements par année scolaire
        if ($anneeScolaire) {
            $paiementsParTypeFraisQuery->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
            });
        }
        
        // Ensuite appliquer les filtres de date seulement si fournis (comme dans entrees)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $paiementsParTypeFraisQuery->whereDate('paiements.date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $paiementsParTypeFraisQuery->whereDate('paiements.date_paiement', '<=', $request->date_fin);
            }
        } else {
            // Si pas de filtres de date supplémentaires, utiliser les dates de l'année scolaire
            if ($anneeScolaire) {
                $paiementsParTypeFraisQuery->whereBetween('paiements.date_paiement', [
                    $anneeScolaire->date_debut->format('Y-m-d'),
                    $anneeScolaire->date_fin->format('Y-m-d')
                ]);
            }
        }
        
        $paiementsParTypeFrais = $paiementsParTypeFraisQuery->groupBy('frais_scolarite.type_frais')
            ->get();
        
        // Fonction pour convertir le type de frais en libellé de source
        $getSourceFromTypeFrais = function($typeFrais) {
            $sources = [
                'inscription' => 'Inscription',
                'reinscription' => 'Réinscription',
                'scolarite' => 'Frais de scolarité',
                'cantine' => 'Cantine',
                'transport' => 'Transport',
                'activites' => 'Activités',
                'autre' => 'Autres frais'
            ];
            return $sources[$typeFrais] ?? 'Autres frais';
        };
        
        // Combiner les entrées manuelles et les paiements par source
        $revenusParTypeCombines = collect();
        foreach ($revenusParType as $revenu) {
            $revenusParTypeCombines->put($revenu->source, [
                'source' => $revenu->source,
                'total' => $revenu->total
            ]);
        }
        
        foreach ($paiementsParTypeFrais as $paiement) {
            $source = $getSourceFromTypeFrais($paiement->type_frais);
            if ($revenusParTypeCombines->has($source)) {
                $item = $revenusParTypeCombines->get($source);
                $item['total'] += $paiement->total;
                $revenusParTypeCombines->put($source, $item);
            } else {
                $revenusParTypeCombines->put($source, [
                    'source' => $source,
                    'total' => $paiement->total
                ]);
            }
        }
        
        $revenusParType = $revenusParTypeCombines->values()->sortByDesc('total')->values();
            
        // Dépenses par type (exclure les salaires enseignants)
        // Filtrer d'abord par année scolaire (comme dans sorties)
        $depensesParCategorieQuery = Depense::query();
        
        if ($anneeScolaire) {
            $depensesParCategorieQuery->whereBetween('date_depense', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Ensuite appliquer les filtres de date supplémentaires seulement si fournis (comme dans sorties)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $depensesParCategorieQuery->whereDate('date_depense', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $depensesParCategorieQuery->whereDate('date_depense', '<=', $request->date_fin);
            }
        }
        
        // Exclure les salaires enseignants
        $depensesParCategorieQuery->where('type_depense', '!=', 'salaire_enseignant');
        
        $depensesParCategorie = $depensesParCategorieQuery->select('type_depense', DB::raw('SUM(montant) as total'))
            ->groupBy('type_depense')
            ->get();
        
        // Ajouter les salaires enseignants comme catégorie (filtrés par année scolaire et dates)
        $totalSalairesEnseignantsQuery = SalaireEnseignant::where('statut', 'payé');
        
        if ($anneeScolaire) {
            $totalSalairesEnseignantsQuery->whereBetween('date_paiement', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Appliquer les filtres de date supplémentaires seulement si fournis (comme dans sorties)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $totalSalairesEnseignantsQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $totalSalairesEnseignantsQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
        }
        
        $totalSalairesEnseignants = $totalSalairesEnseignantsQuery->sum('salaire_net');
        
        if ($totalSalairesEnseignants > 0) {
            $depensesParCategorie->push((object) [
                'type_depense' => 'salaire_enseignant',
                'total' => $totalSalairesEnseignants
            ]);
        }
            
        // Paiements par mode (filtrés par année scolaire et dates)
        // Filtrer d'abord par année scolaire (comme dans entrees)
        $paiementsParModeQuery = Paiement::query();
        
        // Filtrer les paiements par année scolaire
        if ($anneeScolaire) {
            $paiementsParModeQuery->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
            });
        }
        
        // Ensuite appliquer les filtres de date seulement si fournis (comme dans entrees)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $paiementsParModeQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $paiementsParModeQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
        } else {
            // Si pas de filtres de date supplémentaires, utiliser les dates de l'année scolaire
            if ($anneeScolaire) {
                $paiementsParModeQuery->whereBetween('date_paiement', [
                    $anneeScolaire->date_debut->format('Y-m-d'),
                    $anneeScolaire->date_fin->format('Y-m-d')
                ]);
            }
        }
        
        $paiementsParMode = $paiementsParModeQuery->select('mode_paiement', DB::raw('SUM(montant_paye) as total'))
            ->groupBy('mode_paiement')
            ->get();
            
        $statsRevenus = app(ComptabiliteEntreesStatsService::class)->calculateStats(
            $request ?? new Request(),
            $anneeScolaire
        );
        $totalRevenus = $statsRevenus['total'];
            
        // Total des dépenses (dépenses manuelles + salaires enseignants) - utiliser exactement la même logique que sorties()
        // Filtrer d'abord par année scolaire (comme dans sorties)
        $totalDepensesManuellesQuery = Depense::query();
        
        if ($anneeScolaire) {
            $totalDepensesManuellesQuery->whereBetween('date_depense', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Ensuite appliquer les filtres de date supplémentaires seulement si fournis (comme dans sorties)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $totalDepensesManuellesQuery->whereDate('date_depense', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $totalDepensesManuellesQuery->whereDate('date_depense', '<=', $request->date_fin);
            }
        }
        
        // Exclure les salaires enseignants
        $totalDepensesManuellesQuery->where('type_depense', '!=', 'salaire_enseignant');
        
        $totalDepensesManuelles = $totalDepensesManuellesQuery->sum('montant');
        
        // Filtrer les salaires enseignants par année scolaire et dates (comme dans sorties)
        $totalSalairesEnseignantsQuery = SalaireEnseignant::where('statut', 'payé');
        
        if ($anneeScolaire) {
            $totalSalairesEnseignantsQuery->whereBetween('date_paiement', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Appliquer les filtres de date supplémentaires seulement si fournis (comme dans sorties)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $totalSalairesEnseignantsQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $totalSalairesEnseignantsQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
        }
        
        $totalSalairesEnseignants = $totalSalairesEnseignantsQuery->sum('salaire_net');
        
        $totalDepenses = $totalDepensesManuelles + $totalSalairesEnseignants;
            
        return [
            'revenus_par_type' => $revenusParType,
            'depenses_par_categorie' => $depensesParCategorie,
            'paiements_par_mode' => $paiementsParMode,
            'total_revenus' => $totalRevenus,
            'total_depenses' => $totalDepenses,
            'benefice' => $totalRevenus - $totalDepenses
        ];
    }

    /**
     * Obtenir l'évolution des revenus par mois
     */
    private function getEvolutionRevenus()
    {
        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Récupérer les références des paiements pour exclure les entrées correspondantes
        $paiementsQuery = Paiement::where('date_paiement', '>=', Carbon::now()->subMonths(12));
        $paiementsReferences = $paiementsQuery->pluck('reference_paiement')->filter()->toArray();
        
        // Entrées manuelles par mois (exclure les sources automatiques)
        $entreesParMois = Entree::select(
                DB::raw('YEAR(date_entree) as annee'),
                DB::raw('MONTH(date_entree) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->where('date_entree', '>=', Carbon::now()->subMonths(12))
            ->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesParMois->whereNotIn('reference', $paiementsReferences);
        }
        
        $entreesParMois = $entreesParMois->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
        
        // Paiements par mois
        $paiementsParMois = Paiement::select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(montant_paye) as total')
            )
            ->where('date_paiement', '>=', Carbon::now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
        
        // Combiner les entrées manuelles et les paiements par mois
        $revenusParMois = collect();
        foreach ($entreesParMois as $entree) {
            $key = $entree->annee . '-' . $entree->mois;
            $revenusParMois->put($key, [
                'annee' => $entree->annee,
                'mois' => $entree->mois,
                'total' => $entree->total
            ]);
        }
        
        foreach ($paiementsParMois as $paiement) {
            $key = $paiement->annee . '-' . $paiement->mois;
            if ($revenusParMois->has($key)) {
                $item = $revenusParMois->get($key);
                $item['total'] += $paiement->total;
                $revenusParMois->put($key, $item);
            } else {
                $revenusParMois->put($key, [
                    'annee' => $paiement->annee,
                    'mois' => $paiement->mois,
                    'total' => $paiement->total
                ]);
            }
        }
        
        return $revenusParMois->values()->sortBy(function($item) {
            return $item['annee'] * 100 + $item['mois'];
        })->values();
    }

    /**
     * Obtenir l'évolution des dépenses par mois
     */
    private function getEvolutionDepenses()
    {
        // Dépenses manuelles par mois (exclure les salaires enseignants)
        $depensesParMois = Depense::select(
                DB::raw('YEAR(date_depense) as annee'),
                DB::raw('MONTH(date_depense) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->where('date_depense', '>=', Carbon::now()->subMonths(12))
            ->where('type_depense', '!=', 'salaire_enseignant')
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
        
        // Salaires enseignants par mois
        $salairesParMois = SalaireEnseignant::select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(salaire_net) as total')
            )
            ->where('statut', 'payé')
            ->where('date_paiement', '>=', Carbon::now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
        
        // Combiner les dépenses manuelles et les salaires par mois
        $depensesParMoisCombines = collect();
        foreach ($depensesParMois as $depense) {
            $key = $depense->annee . '-' . $depense->mois;
            $depensesParMoisCombines->put($key, [
                'annee' => $depense->annee,
                'mois' => $depense->mois,
                'total' => $depense->total
            ]);
        }
        
        foreach ($salairesParMois as $salaire) {
            $key = $salaire->annee . '-' . $salaire->mois;
            if ($depensesParMoisCombines->has($key)) {
                $item = $depensesParMoisCombines->get($key);
                $item['total'] += $salaire->total;
                $depensesParMoisCombines->put($key, $item);
            } else {
                $depensesParMoisCombines->put($key, [
                    'annee' => $salaire->annee,
                    'mois' => $salaire->mois,
                    'total' => $salaire->total
                ]);
            }
        }
        
        return $depensesParMoisCombines->values()->sortBy(function($item) {
            return $item['annee'] * 100 + $item['mois'];
        })->values();
    }

    /**
     * Obtenir le top 5 des classes par revenus
     */
    private function getTopClassesRevenus($dateDebut, $dateFin)
    {
        return DB::table('paiements')
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->join('eleves', 'frais_scolarite.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
            ->select('classes.nom', DB::raw('SUM(paiements.montant_paye) as total'))
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtenir les statistiques des entrées
     */
    private function getStatsEntrees($request, $anneeScolaireActive = null)
    {
        return app(ComptabiliteEntreesStatsService::class)
            ->calculateStats($request, $anneeScolaireActive);
    }

    /**
     * Obtenir les statistiques des sorties
     */
    private function getStatsSorties($request, $anneeScolaireActive = null)
    {
        return app(ComptabiliteSortiesStatsService::class)
            ->calculateStats($request, $anneeScolaireActive);
    }

    /**
     * Générer le rapport journalier
     */
    public function rapportJournalier(Request $request)
    {
        // Augmenter le timeout pour les rapports volumineux (surtout annuels)
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '1024M'); // 1GB pour les gros rapports
        
        $type = $request->get('type', 'jour');
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $anneeScolaire = null;
        $periodeLabel = '';
        $resumeLabel = '';
        $soldeLabel = 'Solde';

        switch ($type) {
            case 'mois':
                $dateDebut = Carbon::parse($month . '-01')->startOfDay();
                $dateFin = $dateDebut->copy()->endOfMonth()->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Mois de ' . $dateDebut->locale('fr')->isoFormat('MMMM YYYY');
                $resumeLabel = 'Résumé du mois';
                $soldeLabel = 'Solde mensuel';
                break;

            case 'annee':
                $anneeScolaireId = $request->filled('annee_scolaire_id')
                    ? $request->annee_scolaire_id
                    : (\App\Models\AnneeScolaire::anneeActive()?->id);

                $anneeScolaire = $anneeScolaireId
                    ? \App\Models\AnneeScolaire::find($anneeScolaireId)
                    : \App\Models\AnneeScolaire::anneeActive();

                if (!$anneeScolaire) {
                    return redirect()->back()->with('error', 'Aucune année scolaire trouvée.');
                }

                $dateDebut = Carbon::parse($anneeScolaire->date_debut)->startOfDay();
                $dateFin = Carbon::parse($anneeScolaire->date_fin)->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Année scolaire ' . $anneeScolaire->nom;
                $resumeLabel = 'Résumé de l\'année scolaire';
                $soldeLabel = 'Bénéfice (année scolaire)';
                break;

            default:
                $dateDebut = Carbon::parse($date)->startOfDay();
                $dateFin = $dateDebut->copy()->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Journée du ' . $dateDebut->format('d/m/Y');
                $resumeLabel = 'Résumé de la journée';
                $soldeLabel = 'Solde journalier';
                break;
        }

        // Pour le rapport annuel, utiliser un résumé mensuel au lieu de toutes les transactions
        if ($type === 'annee' && $anneeScolaire) {
            $journal = $this->buildJournalMensuelResume($dateDebut, $dateFin, $anneeScolaire);
            $isResumeMensuel = true;
        } else {
            $journal = $this->buildJournalRapport($dateDebut, $dateFin, $anneeScolaire);
            $isResumeMensuel = false;
        }

        // Solde de la période uniquement (pas de cumul historique)
        $soldeInitial = 0;
        $soldeActuel = 0;

        $journal = $journal->map(function ($transaction) use (&$soldeActuel) {
            $soldeActuel += $transaction['entree'] - $transaction['sortie'];
            $transaction['solde'] = $soldeActuel;

            return $transaction;
        });

        if ($type === 'annee' && $anneeScolaire) {
            $statsRevenus = app(ComptabiliteEntreesStatsService::class)
                ->calculateStats(new Request(), $anneeScolaire);
            $statsSorties = app(ComptabiliteSortiesStatsService::class)
                ->calculateStats(new Request(), $anneeScolaire);
            $totalEntrees = $statsRevenus['total'];
            $totalSorties = $statsSorties['total'];
            $soldeFinal = $totalEntrees - $totalSorties;
        } else {
            $totalEntrees = (float) $journal->sum('entree');
            $totalSorties = (float) $journal->sum('sortie');
            $soldeFinal = $totalEntrees - $totalSorties;
        }

        $anneesScolaires = \App\Models\AnneeScolaire::orderBy('date_debut', 'desc')->get();
        $format = $request->get('format', 'html');

        $viewData = compact(
            'journal',
            'date',
            'dateCarbon',
            'soldeInitial',
            'totalEntrees',
            'totalSorties',
            'soldeFinal',
            'type',
            'periodeLabel',
            'resumeLabel',
            'soldeLabel',
            'anneeScolaire',
            'anneesScolaires',
            'dateDebut',
            'dateFin',
            'isResumeMensuel'
        );

        if ($format === 'pdf') {
            try {
                $fileName = 'rapport-journalier';
                if ($type === 'jour') {
                    $fileName .= '-' . $dateCarbon->format('Y-m-d');
                } elseif ($type === 'mois') {
                    $fileName .= '-' . $dateCarbon->format('Y-m');
                } else {
                    $fileName .= '-' . ($anneeScolaire->nom ?? 'annee');
                }
                $fileName .= '.pdf';

                // Ajouter le type de rapport aux données
                $viewData['reportType'] = $type;

                $pdf = Pdf::loadView('comptabilite.rapport-journalier-pdf', $viewData);
                $pdf->setPaper('A4', 'portrait');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', false);
                $pdf->setOption('defaultFont', 'DejaVu Sans');
                $pdf->setOption('fontHeightRatio', 1.1);
                $pdf->setOption('dpi', 96);

                return $pdf->download($fileName);
            } catch (\Exception $e) {
                \Log::error('Erreur génération PDF rapport comptabilité: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            }
        }

        return view('comptabilite.rapport-journalier', $viewData);
    }

    /**
     * Construire un journal résumé mensuel pour les rapports annuels
     * (au lieu de charger toutes les transactions)
     */
    private function buildJournalMensuelResume(Carbon $dateDebut, Carbon $dateFin, \App\Models\AnneeScolaire $anneeScolaire)
    {
        $journal = collect();
        $debutStr = $dateDebut->format('Y-m-d');
        $finStr = $dateFin->format('Y-m-d');
        
        // Parcourir chaque mois de l'année scolaire
        $moisCourant = $dateDebut->copy()->startOfMonth();
        $finAnnee = $dateFin->copy()->endOfMonth();
        
        while ($moisCourant->lte($finAnnee)) {
            $moisDebut = $moisCourant->copy()->startOfMonth();
            $moisFin = $moisCourant->copy()->endOfMonth();
            
            // Limiter à la période de l'année scolaire
            if ($moisDebut->lt($dateDebut)) {
                $moisDebut = $dateDebut->copy();
            }
            if ($moisFin->gt($dateFin)) {
                $moisFin = $dateFin->copy();
            }
            
            $debutMoisStr = $moisDebut->format('Y-m-d');
            $finMoisStr = $moisFin->format('Y-m-d');
            
            // OPTIMISATION: Calculer les totaux directement avec SUM
            $entreesStats = app(ComptabiliteEntreesStatsService::class);
            
            // Total entrées (paiements + entrées manuelles)
            $statsMois = $entreesStats->calculateStats(
                new Request([
                    'date_debut' => $debutMoisStr,
                    'date_fin' => $finMoisStr,
                ]),
                $anneeScolaire
            );
            $totalEntreesMois = $statsMois['total'];
            
            // Total sorties (dépenses + salaires)
            $sortiesStatsService = app(ComptabiliteSortiesStatsService::class);
            $dedupLookupMois = $sortiesStatsService->buildSalaryDedupLookup(
                SalaireEnseignant::where('statut', 'payé')
                    ->whereNotNull('date_paiement')
                    ->whereHas('enseignant', fn ($q) => $q->where('annee_scolaire_id', $anneeScolaire->id))
                    ->whereBetween('date_paiement', [$debutMoisStr, $finMoisStr])
                    ->get(['date_paiement', 'salaire_net'])
            );

            $totalDepensesMois = (float) Depense::where('statut', '!=', 'annule')
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->whereBetween('date_depense', [$debutMoisStr, $finMoisStr])
                ->where('type_depense', '!=', 'salaire_enseignant')
                ->sum('montant');

            foreach (Depense::where('statut', '!=', 'annule')
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->whereBetween('date_depense', [$debutMoisStr, $finMoisStr])
                ->where('type_depense', 'salaire_enseignant')
                ->get() as $depenseSalaire) {
                if (!$sortiesStatsService->depenseLieeAuModuleSalaires($depenseSalaire, $dedupLookupMois)) {
                    $totalDepensesMois += (float) $depenseSalaire->montant;
                }
            }
            
            $totalSalairesMois = SalaireEnseignant::where('statut', 'payé')
                ->whereNotNull('date_paiement')
                ->whereHas('enseignant', fn ($q) => $q->where('annee_scolaire_id', $anneeScolaire->id))
                ->whereBetween('date_paiement', [$debutMoisStr, $finMoisStr])
                ->sum('salaire_net');
            
            $totalSortiesMois = $totalDepensesMois + $totalSalairesMois;
            
            // Ajouter le résumé mensuel au journal
            $journal->push([
                'date' => $moisDebut,
                'libelle' => 'Résumé du mois de ' . $moisDebut->locale('fr')->isoFormat('MMMM YYYY'),
                'entree' => (float) $totalEntreesMois,
                'sortie' => (float) $totalSortiesMois,
                'type' => 'resume_mensuel',
                'source' => 'Résumé mensuel',
                'enregistre_par' => null,
                'created_at' => $moisDebut,
            ]);
            
            // Passer au mois suivant
            $moisCourant->addMonth();
        }
        
        return $journal->sortBy('date')->values();
    }

    /**
     * Construire le journal des transactions pour une période donnée.
     */
    private function buildJournalRapport(Carbon $dateDebut, Carbon $dateFin, ?\App\Models\AnneeScolaire $anneeScolaire = null)
    {
        $journal = collect();
        $debutStr = $dateDebut->format('Y-m-d');
        $finStr = $dateFin->format('Y-m-d');
        $entreesStats = app(ComptabiliteEntreesStatsService::class);
        $anneeScolaire = $anneeScolaire ?? \App\Models\AnneeScolaire::anneeActive();

        if ($anneeScolaire) {
            foreach ($entreesStats->buildJournalEntrees($dateDebut, $dateFin, $anneeScolaire) as $ligne) {
                $journal->push($ligne);
            }
        } else {
            $entrees = Entree::with('enregistrePar')
                ->whereBetween('date_entree', [$debutStr, $finStr])
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($entrees as $entree) {
                $journal->push([
                    'date' => $entree->date_entree,
                    'libelle' => $entree->description ?? $entree->libelle,
                    'entree' => (float) $entree->montant,
                    'sortie' => 0,
                    'type' => 'entree_manuelle',
                    'source' => $entree->source,
                    'enregistre_par' => $entree->enregistrePar,
                    'created_at' => $entree->created_at,
                ]);
            }
        }

        $sortiesStatsService = app(ComptabiliteSortiesStatsService::class);

        $depenses = Depense::with(['approuvePar', 'payePar'])
            ->where('statut', '!=', 'annule')
            ->whereBetween('date_depense', [$debutStr, $finStr])
            ->orderBy('created_at', 'asc')
            ->get();

        $salairesPayes = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [$debutStr, $finStr])
            ->with(['enseignant.utilisateur', 'payePar'])
            ->orderBy('created_at', 'asc')
            ->get();

        $dedupLookup = $sortiesStatsService->buildSalaryDedupLookup($salairesPayes);

        foreach ($depenses as $depense) {
            if ($sortiesStatsService->depenseLieeAuModuleSalaires($depense, $dedupLookup)) {
                continue;
            }

            $journal->push([
                'date' => $depense->date_depense,
                'libelle' => $depense->libelle,
                'entree' => 0,
                'sortie' => (float) $depense->montant,
                'type' => 'depense',
                'source' => $depense->type_depense,
                'enregistre_par' => $depense->approuvePar ?? $depense->payePar,
                'created_at' => $depense->created_at,
            ]);
        }

        foreach ($salairesPayes as $salaire) {
            $enseignantNom = $salaire->enseignant && $salaire->enseignant->utilisateur
                ? ($salaire->enseignant->utilisateur->prenom . ' ' . $salaire->enseignant->utilisateur->nom)
                : 'Enseignant inconnu';

            $journal->push([
                'date' => $salaire->date_paiement,
                'libelle' => 'Salaire - ' . $enseignantNom . ' ('
                    . ($salaire->periode_debut ? $salaire->periode_debut->format('d/m/Y') : 'N/A') . ' - '
                    . ($salaire->periode_fin ? $salaire->periode_fin->format('d/m/Y') : 'N/A') . ')',
                'entree' => 0,
                'sortie' => (float) ($salaire->salaire_net ?? 0),
                'type' => 'salaire_enseignant',
                'source' => 'Salaire enseignant',
                'enregistre_par' => $salaire->payePar,
                'created_at' => $salaire->created_at,
            ]);
        }

        return $journal->sortByDesc('created_at')->values();
    }

    /**
     * Une dépense est-elle déjà représentée par un salaire enseignant payé ?
     */
    private function depenseCorrespondSalairePaye(Depense $depense, SalaireEnseignant $salaire): bool
    {
        return $depense->date_depense->format('Y-m-d') === $salaire->date_paiement->format('Y-m-d')
            && abs((float) $depense->montant - (float) $salaire->salaire_net) < 0.01;
    }

    /**
     * Rapport des élèves n'ayant pas payé les mois sélectionnés, par classe.
     */
    public function impayesMensuels(Request $request, FacturationService $facturationService)
    {
        set_time_limit(120);

        $anneesScolaires = \App\Models\AnneeScolaire::orderByDesc('date_debut')->get();
        $anneeScolaire = $request->filled('annee_scolaire_id')
            ? \App\Models\AnneeScolaire::find($request->annee_scolaire_id)
            : \App\Models\AnneeScolaire::anneeActive();

        if (!$anneeScolaire) {
            return redirect()->route('comptabilite.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $classes = Classe::orderBy('nom')->get();
        $moisOptions = $facturationService->getMoisFacturationOptions($anneeScolaire);
        $typesFrais = [
            'scolarite' => 'Scolarité',
            'cantine' => 'Cantine',
            'transport' => 'Transport',
        ];

        $moisSelectionnes = array_values(array_filter((array) $request->input('mois', [])));
        $classeId = $request->filled('classe_id') ? (int) $request->classe_id : null;
        $typeFrais = $request->input('type_frais', 'scolarite');
        $resultats = collect();
        $aRecherche = false;

        if ($request->has('filtrer')) {
            $request->validate([
                'classe_id' => 'required|exists:classes,id',
                'mois' => 'required|array|min:1',
                'mois.*' => 'string|regex:/^\d{4}-\d{2}$/',
                'type_frais' => 'in:scolarite,cantine,transport',
            ], [
                'classe_id.required' => 'Veuillez sélectionner une classe.',
                'mois.required' => 'Veuillez cocher au moins un mois.',
                'mois.min' => 'Veuillez cocher au moins un mois.',
            ]);

            $aRecherche = true;
            $resultats = $facturationService->rechercherElevesImpayes(
                $anneeScolaire,
                $moisSelectionnes,
                $classeId,
                $typeFrais
            );
        }

        return view('comptabilite.impayes-mensuels', compact(
            'anneeScolaire',
            'anneesScolaires',
            'classes',
            'moisOptions',
            'typesFrais',
            'moisSelectionnes',
            'classeId',
            'typeFrais',
            'resultats',
            'aRecherche'
        ));
    }
}
