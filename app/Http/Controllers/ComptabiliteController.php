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

class ComptabiliteController extends Controller
{
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
            
            // Statistiques générales pour l'année active
            $stats = $this->getComptabiliteStats($anneeScolaireActive);
        
        $entreesStats = app(ComptabiliteEntreesStatsService::class);

        $toutesLesEntrees = $entreesStats
            ->buildListEntries(new Request(), $anneeScolaireActive)
            ->sortByDesc(fn ($entry) => $entry->date instanceof \Carbon\Carbon ? $entry->date->timestamp : strtotime((string) $entry->date))
            ->take(10);

        $sortiesStats = app(ComptabiliteSortiesStatsService::class);
        $toutesLesSorties = $sortiesStats
            ->buildListEntries(new Request(), $anneeScolaireActive)
            ->sortByDesc(fn ($entry) => $entry->date instanceof \Carbon\Carbon ? $entry->date->timestamp : strtotime((string) $entry->date))
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
     */
    private function getEvolutionData($anneeScolaire)
    {
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
            
            $statsMois = app(ComptabiliteEntreesStatsService::class)->calculateStats(
                new Request([
                    'date_debut' => $moisDebut->format('Y-m-d'),
                    'date_fin' => $moisFin->format('Y-m-d'),
                ]),
                $anneeScolaire
            );
            $revenus[] = $statsMois['total'];

            $statsSortiesMois = app(ComptabiliteSortiesStatsService::class)->calculateStats(
                new Request([
                    'date_debut' => $moisDebut->format('Y-m-d'),
                    'date_fin' => $moisFin->format('Y-m-d'),
                ]),
                $anneeScolaire
            );
            $depenses[] = $statsSortiesMois['total'];
        }
        
        return [
            'labels' => $mois,
            'revenus' => $revenus,
            'depenses' => $depenses
        ];
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
        // Récupérer l'année scolaire (filtrée ou active par défaut)
        $anneeScolaireId = $request->filled('annee_scolaire_id') 
            ? $request->annee_scolaire_id 
            : (\App\Models\AnneeScolaire::anneeActive()?->id);
        
        $anneeScolaire = $anneeScolaireId 
            ? \App\Models\AnneeScolaire::find($anneeScolaireId)
            : \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire trouvée. Veuillez sélectionner une année scolaire.');
        }
        
        // Récupérer les entrées (manuelles, factures payées, paiements hors facture)
        $entreesStats = app(ComptabiliteEntreesStatsService::class);
        $allEntries = $entreesStats->buildListEntries($request, $anneeScolaire);

        $perPage = 50;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allEntries->slice($offset, $perPage);
        
        // Créer un objet de pagination personnalisé
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
        
        // Ajouter les paramètres de requête à la pagination
        $paginatedEntries->appends(request()->query());
        
        // Statistiques des entrées (filtrées par année scolaire sélectionnée)
        $statsEntrees = $this->getStatsEntrees($request, $anneeScolaire);
        
        // Sources disponibles pour les filtres basées sur les types de frais des paiements
        $sources = [];
        
        // Récupérer les types de frais uniques des paiements de l'année sélectionnée
        $typesFraisQuery = \App\Models\Paiement::sansFacture()->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
            if ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
            }
        })
        ->whereHas('fraisScolarite', function($q) {
            $q->whereNotNull('type_frais');
        })
        ->with('fraisScolarite');
        
        $paiementsPourSources = $typesFraisQuery->get();
        
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
        
        // Récupérer les sources uniques à partir des types de frais
        foreach ($paiementsPourSources as $paiement) {
            $typeFrais = $paiement->fraisScolarite->type_frais ?? 'autre';
            $source = $getSourceFromTypeFrais($typeFrais);
            if (!in_array($source, $sources)) {
                $sources[] = $source;
            }
        }
        
        // Ajouter aussi les sources des entrées manuelles (non liées aux paiements)
        $sourcesEntreesQuery = Entree::query();
        if ($anneeScolaire) {
            $sourcesEntreesQuery->whereBetween('date_entree', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        // Exclure les sources automatiques créées par les paiements
        $sourcesEntreesQuery->whereNotIn('source', ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires']);
        $sourcesEntrees = $sourcesEntreesQuery->select('source')->distinct()->orderBy('source')->pluck('source')->toArray();
        
        // Combiner les sources
        $sources = array_merge($sources, $sourcesEntrees);
        if (\App\Models\Facture::where('statut', 'payee')->where('annee_scolaire_id', $anneeScolaire->id)->exists()) {
            $sources[] = 'Frais de scolarité';
        }
        $sources = array_unique($sources);
        sort($sources);
        
        $sources = collect($sources);
        
        // Récupérer toutes les années scolaires pour le filtre
        $anneesScolaires = \App\Models\AnneeScolaire::orderBy('date_debut', 'desc')->get();
        
        return view('comptabilite.entrees', compact('paginatedEntries', 'statsEntrees', 'sources', 'anneeScolaire', 'anneesScolaires'));
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
        $allSorties = $sortiesStatsService->sortByDateDesc(
            $sortiesStatsService->buildListEntries($request, $anneeScolaire)
        );

        // Créer une pagination manuelle (comme dans entrees)
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allSorties->slice($offset, $perPage)->values();
        
        // Créer un objet de pagination personnalisé avec la collection
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
        
        // Ajouter les paramètres de requête à la pagination
        $sorties->appends(request()->query());
        
        $statsSorties = $this->getStatsSorties($request, $anneeScolaire);

        $periodeSorties = $sortiesStatsService->effectiveSchoolYearDateRange($anneeScolaire);

        $typesDepenseQuery = Depense::query()
            ->whereBetween('date_depense', [$periodeSorties['debut'], $periodeSorties['fin']])
            ->where('type_depense', '!=', 'salaire_enseignant');

        $typesDepense = $typesDepenseQuery->select('type_depense')
            ->distinct()
            ->orderBy('type_depense')
            ->pluck('type_depense')
            ->toArray();

        $salairesDansAnnee = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [$periodeSorties['debut'], $periodeSorties['fin']])
            ->exists();

        if ($salairesDansAnnee && !in_array('salaire_enseignant', $typesDepense, true)) {
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
            $totaux = app(ComptabiliteEntreesStatsService::class)
                ->totauxAnneeScolaireOfficielle($anneeScolaireActive);
            $revenusTotal = $totaux['total_entrees'];
            $depensesTotal = $totaux['total_sorties'];
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
        $paiementsQuery = Paiement::query()->sansFacture();
        
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
        $paiementsParTypeFraisQuery = Paiement::sansFacture()
            ->select('frais_scolarite.type_frais', DB::raw('SUM(paiements.montant_paye) as total'))
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
        $paiementsParModeQuery = Paiement::query()->sansFacture();
        
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
        $paiementsQuery = Paiement::sansFacture()->where('date_paiement', '>=', Carbon::now()->subMonths(12));
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
        $paiementsParMois = Paiement::sansFacture()->select(
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
        $type = $request->get('type', 'jour');
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $periodeLabel = '';
        $resumeLabel = '';
        $soldeLabel = 'Solde';

        $anneeScolaire = $this->resolveAnneeScolaireForRapport($request);
        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire trouvée.');
        }

        switch ($type) {
            case 'mois':
                $dateDebut = Carbon::parse($month . '-01')->startOfDay();
                $dateFin = $dateDebut->copy()->endOfMonth()->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Mois de ' . $dateDebut->locale('fr')->isoFormat('MMMM YYYY')
                    . ' — année scolaire ' . $anneeScolaire->nom;
                $resumeLabel = 'Résumé du mois';
                $soldeLabel = 'Solde mensuel';
                break;

            case 'annee':
                $dateDebut = Carbon::parse($anneeScolaire->date_debut)->startOfDay();
                $dateFin = Carbon::parse($anneeScolaire->date_fin)->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Année scolaire ' . $anneeScolaire->nom
                    . ' (' . $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y') . ')';
                $resumeLabel = 'Résumé de l\'année scolaire';
                $soldeLabel = 'Bénéfice (année scolaire)';
                break;

            default:
                $dateDebut = Carbon::parse($date)->startOfDay();
                $dateFin = $dateDebut->copy()->endOfDay();
                $dateCarbon = $dateDebut;
                $periodeLabel = 'Journée du ' . $dateDebut->format('d/m/Y')
                    . ' — année scolaire ' . $anneeScolaire->nom;
                $resumeLabel = 'Résumé de la journée';
                $soldeLabel = 'Solde journalier';
                break;
        }

        $journal = $this->buildJournalPeriode(
            $dateDebut,
            $dateFin,
            $anneeScolaire,
            $type === 'annee'
        );

        // Solde de la période uniquement (pas de cumul historique)
        $soldeInitial = 0;
        $soldeActuel = 0;

        $journal = $journal->map(function ($transaction) use (&$soldeActuel) {
            $soldeActuel += $transaction['entree'] - $transaction['sortie'];
            $transaction['solde'] = $soldeActuel;

            return $transaction;
        });

        $totalEntrees = (float) $journal->sum('entree');
        $totalSorties = (float) $journal->sum('sortie');
        $soldeFinal = $totalEntrees - $totalSorties;

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
            'dateFin'
        );

        if ($format === 'pdf') {
            $fileName = 'rapport-journalier';
            if ($type === 'jour') {
                $fileName .= '-' . $dateCarbon->format('Y-m-d');
            } elseif ($type === 'mois') {
                $fileName .= '-' . $dateCarbon->format('Y-m');
            } else {
                $fileName .= '-' . ($anneeScolaire->nom ?? 'annee');
            }
            $fileName .= '.pdf';

            $pdf = Pdf::loadView('comptabilite.rapport-journalier-pdf', $viewData);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');
            $pdf->setOption('fontHeightRatio', 1.1);

            $fontsPath = storage_path('fonts');
            if (!is_dir($fontsPath)) {
                mkdir($fontsPath, 0755, true);
            }

            return $pdf->download($fileName);
        }

        return view('comptabilite.rapport-journalier', $viewData);
    }

    private function resolveAnneeScolaireForRapport(Request $request): ?\App\Models\AnneeScolaire
    {
        $anneeScolaireId = $request->filled('annee_scolaire_id')
            ? $request->annee_scolaire_id
            : (\App\Models\AnneeScolaire::anneeActive()?->id);

        return $anneeScolaireId
            ? \App\Models\AnneeScolaire::find($anneeScolaireId)
            : \App\Models\AnneeScolaire::anneeActive();
    }

    /**
     * Journal unifié pour jour, mois ou année scolaire (intersection avec l'année scolaire).
     */
    private function buildJournalPeriode(
        Carbon $dateDebut,
        Carbon $dateFin,
        \App\Models\AnneeScolaire $anneeScolaire,
        bool $anneeComplete = false
    ) {
        $anneeDebut = Carbon::parse($anneeScolaire->date_debut)->startOfDay();
        $anneeFin = Carbon::parse($anneeScolaire->date_fin)->endOfDay();

        $effectiveDebut = $dateDebut->greaterThan($anneeDebut) ? $dateDebut->copy() : $anneeDebut->copy();
        $effectiveFin = $dateFin->lessThan($anneeFin) ? $dateFin->copy() : $anneeFin->copy();

        if ($effectiveDebut->gt($effectiveFin)) {
            return collect();
        }

        $entreesStats = app(ComptabiliteEntreesStatsService::class);
        $sortiesStats = app(ComptabiliteSortiesStatsService::class);

        if ($anneeComplete) {
            $reportRequest = $entreesStats->requestAnneeScolaireComplete($anneeScolaire);
        } else {
            $reportRequest = new Request([
                'date_debut' => $effectiveDebut->format('Y-m-d'),
                'date_fin' => $effectiveFin->format('Y-m-d'),
            ]);
        }

        $journal = collect();

        foreach ($entreesStats->buildListEntries($reportRequest, $anneeScolaire) as $entry) {
            $libelle = $entry->description;
            if (!empty($entry->detail)) {
                $libelle .= ' — ' . $entry->detail;
            }

            $journal->push([
                'date' => $entry->date,
                'libelle' => $libelle,
                'entree' => (float) $entry->montant,
                'sortie' => 0,
                'type' => $entry->type,
                'source' => $entry->source,
                'enregistre_par' => $entry->enregistre_par,
                'created_at' => $entry->data->created_at ?? $entry->date,
            ]);
        }

        foreach ($sortiesStats->buildListEntries($reportRequest, $anneeScolaire) as $entry) {
            $journal->push([
                'date' => $entry->date,
                'libelle' => $entry->libelle ?? $entry->description,
                'entree' => 0,
                'sortie' => (float) $entry->montant,
                'type' => $entry->type,
                'source' => $entry->type_depense ?? 'depense',
                'enregistre_par' => $entry->enregistre_par,
                'created_at' => $entry->data->created_at ?? $entry->date,
            ]);
        }

        return $this->sortJournalChronologique($journal);
    }

    /**
     * Tri chronologique du journal : plus ancien → plus récent.
     */
    private function sortJournalChronologique($journal)
    {
        return $journal->sortBy(function ($transaction) {
            $dateTs = $transaction['date'] instanceof Carbon
                ? $transaction['date']->timestamp
                : strtotime((string) $transaction['date']);
            $createdTs = $transaction['created_at'] instanceof Carbon
                ? $transaction['created_at']->timestamp
                : strtotime((string) $transaction['created_at']);

            return sprintf('%010d_%010d', $dateTs, $createdTs);
        })->values();
    }
}
