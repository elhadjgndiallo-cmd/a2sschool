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

class ComptabiliteController extends Controller
{
    /**
     * Afficher le tableau de bord de la comptabilité
     */
    public function index()
    {
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        // Statistiques générales pour l'année active
        $stats = $this->getComptabiliteStats($anneeScolaireActive);
        
        // Dernières transactions de l'année active
        $dernieresEntrees = collect(); // Les entrées manuelles ne sont pas liées à une année scolaire
            
        // Ajouter les derniers paiements de frais de scolarité de l'année active
        $derniersPaiements = collect();
        if ($anneeScolaireActive) {
            $derniersPaiements = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
                ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                    $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                })
                ->orderBy('date_paiement', 'desc')
                ->limit(5)
                ->get();
        }
        
        // Combiner les entrées manuelles et les paiements
        $toutesLesEntrees = $dernieresEntrees->concat($derniersPaiements)
            ->sortByDesc(function($item) {
                return $item->created_at ?? $item->date_paiement;
            })
            ->take(5);
            
        // Filtrer les dépenses par la période de l'année scolaire active
        $dernieresDepenses = collect();
        if ($anneeScolaireActive) {
            $dernieresDepenses = Depense::with(['approuvePar', 'payePar'])
                ->whereBetween('date_depense', [
                    $anneeScolaireActive->date_debut,
                    $anneeScolaireActive->date_fin
                ])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        return view('comptabilite.index', compact('stats', 'toutesLesEntrees', 'dernieresDepenses'));
    }

    /**
     * Afficher les rapports de comptabilité
     */
    public function rapports(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth());
        $dateFin = $request->get('date_fin', Carbon::now()->endOfMonth());
        
        // Rapports financiers
        $rapports = $this->genererRapports($dateDebut, $dateFin);
        
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
            'dateFin'
        ));
    }

    /**
     * Afficher les entrées (revenus)
     */
    public function entrees(Request $request)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Récupérer les entrées manuelles (filtrées par période de l'année scolaire active)
        $query = Entree::with('enregistrePar');
        
        // Filtrer par période de l'année scolaire active
        // Convertir les dates en format string pour éviter les problèmes de comparaison
        if ($anneeScolaireActive) {
            $query->whereBetween('date_entree', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        
        // Filtres supplémentaires
        if ($request->filled('date_debut')) {
            $query->whereDate('date_entree', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_entree', '<=', $request->date_fin);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        $entrees = $query->orderBy('date_entree', 'desc')->get();

        // Récupérer les paiements de frais de scolarité de l'année active seulement
        $paiementsFrais = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->orderBy('date_paiement', 'desc')
            ->get();

        // Combiner les deux collections et créer une pagination unifiée
        $allEntries = collect();
        
        // Ajouter les entrées manuelles avec un type
        foreach ($entrees as $entree) {
            $allEntries->push((object) [
                'id' => 'entree_' . $entree->id,
                'type' => 'entree',
                'date' => $entree->date_entree,
                'description' => $entree->description,
                'montant' => $entree->montant,
                'source' => $entree->source,
                'enregistre_par' => $entree->enregistrePar,
                'data' => $entree
            ]);
        }
        
        // Ajouter tous les paiements de frais de scolarité de l'année active
        foreach ($paiementsFrais as $paiement) {
            // Vérifier si le paiement correspond aux filtres de date si spécifiés
            if ($request->filled('date_debut') && $paiement->date_paiement < $request->date_debut) {
                continue;
            }
            
            if ($request->filled('date_fin') && $paiement->date_paiement > $request->date_fin) {
                continue;
            }
            
            // Récupérer l'élève pour la description
            $eleve = $paiement->fraisScolarite->eleve ?? null;
            $eleveNom = $eleve && $eleve->utilisateur ? 
                ($eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom) : 
                'Élève inconnu';
            
            $description = 'Paiement de ' . number_format($paiement->montant_paye, 0, ',', ' ') . ' GNF - ' . $eleveNom;
            $source = 'Frais de scolarité';
            
            // Appliquer le filtre de source si spécifié
            if ($request->filled('source') && $source !== $request->source) {
                continue; // Ignorer ce paiement s'il ne correspond pas au filtre
            }
            
            $allEntries->push((object) [
                'id' => 'paiement_' . $paiement->id,
                'type' => 'paiement',
                'date' => $paiement->date_paiement,
                'description' => $description,
                'montant' => $paiement->montant_paye,
                'source' => $source,
                'enregistre_par' => $paiement->encaissePar,
                'data' => $paiement
            ]);
        }
        
        // Trier par date décroissante
        $allEntries = $allEntries->sortByDesc('date');
        
        // Créer une pagination manuelle
        $perPage = 20;
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
        
        // Statistiques des entrées (filtrées par année scolaire active)
        $statsEntrees = $this->getStatsEntrees($request, $anneeScolaireActive);
        
        // Sources disponibles pour les filtres (filtrées par année scolaire active)
        $sourcesQuery = Entree::query();
        if ($anneeScolaireActive) {
            $sourcesQuery->whereBetween('date_entree', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        $sources = $sourcesQuery->select('source')->distinct()->orderBy('source')->pluck('source')->toArray();
        
        // Ajouter "Frais de scolarité" si des paiements existent pour l'année active
        if ($paiementsFrais->count() > 0 && !in_array('Frais de scolarité', $sources)) {
            $sources[] = 'Frais de scolarité';
            sort($sources);
        }
        
        $sources = collect($sources);
        
        return view('comptabilite.entrees', compact('paginatedEntries', 'statsEntrees', 'sources', 'anneeScolaireActive'));
    }

    /**
     * Afficher les sorties (dépenses)
     */
    public function sorties(Request $request)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $query = Depense::with(['approuvePar', 'payePar']);
        
        // Filtrer par période de l'année scolaire active (exactement comme dans entrees)
        // Convertir les dates en format string pour éviter les problèmes de comparaison
        if ($anneeScolaireActive) {
            $query->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        
        // Filtres supplémentaires
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }
        
        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }
        
        $depenses = $query->orderBy('date_depense', 'desc')->get();
        
        // Debug: vérifier les dépenses récupérées
        \Log::info('Debug sorties', [
            'annee_active' => $anneeScolaireActive->nom,
            'date_debut' => $anneeScolaireActive->date_debut->format('Y-m-d'),
            'date_fin' => $anneeScolaireActive->date_fin->format('Y-m-d'),
            'depenses_count' => $depenses->count(),
            'query_sql' => $query->toSql(),
            'query_bindings' => $query->getBindings()
        ]);
        
        // Récupérer les salaires d'enseignants payés de l'année active seulement
        $salairesPayes = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ])
            ->with(['enseignant.utilisateur', 'payePar'])
            ->orderBy('date_paiement', 'desc')
            ->get();
        
        // Combiner les deux collections et créer une pagination unifiée
        $allSorties = collect();
        
        // Ajouter les dépenses avec un type
        foreach ($depenses as $depense) {
            // Vérifier si cette dépense correspond à un salaire (pour éviter les doublons)
            $correspondSalaire = false;
            foreach ($salairesPayes as $salaire) {
                if ($depense->type_depense === 'salaire_enseignant' && 
                    $depense->date_depense == $salaire->date_paiement &&
                    abs($depense->montant - $salaire->salaire_net) < 0.01) {
                    $correspondSalaire = true;
                    break;
                }
            }
            
            // Si c'est une dépense de salaire déjà représentée par un salaire payé, on peut la garder ou la sauter
            // On garde toutes les dépenses pour être cohérent
            $allSorties->push((object) [
                'id' => 'depense_' . $depense->id,
                'type' => 'depense',
                'date' => $depense->date_depense,
                'libelle' => $depense->libelle,
                'montant' => $depense->montant,
                'type_depense' => $depense->type_depense,
                'description' => $depense->description,
                'approuve_par' => $depense->approuvePar,
                'paye_par' => $depense->payePar,
                'data' => $depense
            ]);
        }
        
        // Ajouter TOUS les salaires d'enseignants payés de l'année active
        foreach ($salairesPayes as $salaire) {
            // Vérifier les filtres supplémentaires (dates)
            if ($request->filled('date_debut') && $salaire->date_paiement && $salaire->date_paiement < $request->date_debut) {
                continue;
            }
            
            if ($request->filled('date_fin') && $salaire->date_paiement && $salaire->date_paiement > $request->date_fin) {
                continue;
            }
            
            // Filtrer par type seulement si spécifié
            if ($request->filled('type_depense') && $request->type_depense !== 'salaire_enseignant') {
                continue;
            }
            
            $enseignantNom = $salaire->enseignant && $salaire->enseignant->utilisateur ? 
                ($salaire->enseignant->utilisateur->prenom . ' ' . $salaire->enseignant->utilisateur->nom) : 
                'Enseignant inconnu';
            
            $allSorties->push((object) [
                'id' => 'salaire_' . $salaire->id,
                'type' => 'salaire',
                'date' => $salaire->date_paiement,
                'libelle' => 'Salaire - ' . $enseignantNom . ' (' . ($salaire->periode_debut ? $salaire->periode_debut->format('d/m/Y') : 'N/A') . ' - ' . ($salaire->periode_fin ? $salaire->periode_fin->format('d/m/Y') : 'N/A') . ')',
                'montant' => $salaire->salaire_net ?? 0,
                'type_depense' => 'salaire_enseignant',
                'description' => 'Paiement de salaire pour la période ' . ($salaire->periode_debut ? $salaire->periode_debut->format('d/m/Y') : 'N/A') . ' - ' . ($salaire->periode_fin ? $salaire->periode_fin->format('d/m/Y') : 'N/A'),
                'approuve_par' => $salaire->validePar ?? null,
                'paye_par' => $salaire->payePar ?? null,
                'data' => $salaire
            ]);
        }
        
        // Trier par date décroissante (comme dans entrees)
        $allSorties = $allSorties->sortByDesc(function($item) {
            return $item->date;
        })->values();
        
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
        
        // Statistiques des sorties (filtrées par année scolaire active)
        $statsSorties = $this->getStatsSorties($request, $anneeScolaireActive);
        
        // Types de dépenses disponibles pour les filtres (filtrés par année scolaire active)
        $typesDepenseQuery = Depense::query();
        if ($anneeScolaireActive) {
            $typesDepenseQuery->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        $typesDepense = $typesDepenseQuery->select('type_depense')->distinct()->orderBy('type_depense')->pluck('type_depense')->toArray();
        
        // Ajouter "salaire_enseignant" si des salaires payés existent pour l'année active
        if ($salairesPayes->count() > 0 && !in_array('salaire_enseignant', $typesDepense)) {
            $typesDepense[] = 'salaire_enseignant';
            sort($typesDepense);
        }
        
        $typesDepense = collect($typesDepense);
        
        return view('comptabilite.sorties', compact('sorties', 'statsSorties', 'anneeScolaireActive', 'typesDepense'));
    }

    /**
     * Obtenir les statistiques générales de la comptabilité
     */
    private function getComptabiliteStats($anneeScolaireActive = null)
    {
        $moisActuel = Carbon::now();
        
        // Revenus du mois actuel (entrées manuelles) - filtrer par période de l'année scolaire
        $revenusMois = 0;
        if ($anneeScolaireActive) {
            $revenusMois = Entree::whereMonth('date_entree', $moisActuel->month)
                ->whereYear('date_entree', $moisActuel->year)
                ->whereBetween('date_entree', [
                    $anneeScolaireActive->date_debut,
                    $anneeScolaireActive->date_fin
                ])
                ->sum('montant');
        }
            
        // Ajouter les paiements de frais de scolarité du mois pour l'année active
        if ($anneeScolaireActive) {
            $paiementsMois = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->whereMonth('date_paiement', $moisActuel->month)
            ->whereYear('date_paiement', $moisActuel->year)
            ->sum('montant_paye');
            
            $revenusMois += $paiementsMois;
        }
            
        // Dépenses du mois actuel - filtrer par période de l'année scolaire
        $depensesMois = 0;
        if ($anneeScolaireActive) {
            $depensesMois = Depense::whereMonth('date_depense', $moisActuel->month)
                ->whereYear('date_depense', $moisActuel->year)
                ->whereBetween('date_depense', [
                    $anneeScolaireActive->date_debut,
                    $anneeScolaireActive->date_fin
                ])
                ->sum('montant');
        }
            
        // Revenus totaux (entrées manuelles) - filtrer par période de l'année scolaire
        $revenusTotal = 0;
        if ($anneeScolaireActive) {
            $revenusTotal = Entree::whereBetween('date_entree', [
                $anneeScolaireActive->date_debut,
                $anneeScolaireActive->date_fin
            ])->sum('montant');
        }
        
        // Ajouter les paiements de frais de scolarité totaux pour l'année active
        if ($anneeScolaireActive) {
            $paiementsTotal = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })->sum('montant_paye');
            
            $revenusTotal += $paiementsTotal;
        }
        
        // Dépenses totales - filtrer par période de l'année scolaire
        $depensesTotal = 0;
        if ($anneeScolaireActive) {
            $depensesTotal = Depense::whereBetween('date_depense', [
                $anneeScolaireActive->date_debut,
                $anneeScolaireActive->date_fin
            ])->sum('montant');
        }
        
        // Bénéfice du mois
        $beneficeMois = $revenusMois - $depensesMois;
        
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
            'revenus_mois' => $revenusMois,
            'depenses_mois' => $depensesMois,
            'benefice_mois' => $beneficeMois,
            'revenus_total' => $revenusTotal,
            'depenses_total' => $depensesTotal,
            'benefice_total' => $beneficeTotal,
            'eleves_en_attente' => $elevesEnAttente
        ];
    }

    /**
     * Générer les rapports financiers
     */
    private function genererRapports($dateDebut, $dateFin)
    {
        // Revenus par source
        $revenusParType = Entree::whereBetween('date_entree', [$dateDebut, $dateFin])
            ->select('source', DB::raw('SUM(montant) as total'))
            ->groupBy('source')
            ->get();
            
        // Dépenses par type
        $depensesParCategorie = Depense::whereBetween('date_depense', [$dateDebut, $dateFin])
            ->select('type_depense', DB::raw('SUM(montant) as total'))
            ->groupBy('type_depense')
            ->get();
            
        // Paiements par mode
        $paiementsParMode = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->select('mode_paiement', DB::raw('SUM(montant_paye) as total'))
            ->groupBy('mode_paiement')
            ->get();
            
        // Total des revenus
        $totalRevenus = Entree::whereBetween('date_entree', [$dateDebut, $dateFin])
            ->sum('montant');
            
        // Total des dépenses
        $totalDepenses = Depense::whereBetween('date_depense', [$dateDebut, $dateFin])
            ->sum('montant');
            
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
        return Entree::select(
                DB::raw('YEAR(date_entree) as annee'),
                DB::raw('MONTH(date_entree) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->where('date_entree', '>=', Carbon::now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
    }

    /**
     * Obtenir l'évolution des dépenses par mois
     */
    private function getEvolutionDepenses()
    {
        return Depense::select(
                DB::raw('YEAR(date_depense) as annee'),
                DB::raw('MONTH(date_depense) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->where('date_depense', '>=', Carbon::now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();
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
        $query = Entree::query();
        
        // Filtrer par période de l'année scolaire active
        // Convertir les dates en format string pour éviter les problèmes de comparaison
        if ($anneeScolaireActive) {
            $query->whereBetween('date_entree', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        
        if ($request->filled('date_debut')) {
            $query->whereDate('date_entree', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_entree', '<=', $request->date_fin);
        }
        
        // Ajouter les paiements de frais de scolarité de l'année active
        $totalEntrees = $query->sum('montant');
        $nombreEntrees = $query->count();
        
        // Ajouter les paiements de frais de scolarité
        if ($anneeScolaireActive) {
            $paiementsQuery = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
            
            if ($request->filled('date_debut')) {
                $paiementsQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            
            if ($request->filled('date_fin')) {
                $paiementsQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
            
            $totalPaiements = $paiementsQuery->sum('montant_paye');
            $nombrePaiements = $paiementsQuery->count();
            
            $totalEntrees += $totalPaiements;
            $nombreEntrees += $nombrePaiements;
        }
        
        $moyenne = $nombreEntrees > 0 ? ($totalEntrees / $nombreEntrees) : 0;
        
        return [
            'total' => $totalEntrees,
            'nombre' => $nombreEntrees,
            'moyenne' => $moyenne
        ];
    }

    /**
     * Obtenir les statistiques des sorties
     */
    private function getStatsSorties($request, $anneeScolaireActive = null)
    {
        $query = Depense::query();
        
        // Filtrer par période de l'année scolaire active (exactement comme dans entrees)
        // Convertir les dates en format string pour éviter les problèmes de comparaison
        if ($anneeScolaireActive) {
            $query->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }
        
        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }
        
        $totalDepenses = $query->sum('montant');
        $nombreDepenses = $query->count();
        
        // Ajouter les salaires d'enseignants payés de l'année active
        $salairesQuery = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement');
        
        if ($anneeScolaireActive) {
            $salairesQuery->whereBetween('date_paiement', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        }
        
        if ($request->filled('date_debut')) {
            $salairesQuery->whereDate('date_paiement', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $salairesQuery->whereDate('date_paiement', '<=', $request->date_fin);
        }
        
        // Filtrer par type si spécifié (seulement pour salaire_enseignant)
        if ($request->filled('type_depense')) {
            if ($request->type_depense !== 'salaire_enseignant') {
                // Si ce n'est pas salaire_enseignant, ne pas inclure les salaires
                $totalSalaires = 0;
                $nombreSalaires = 0;
            } else {
                // Inclure seulement les salaires
                $totalSalaires = $salairesQuery->sum('salaire_net');
                $nombreSalaires = $salairesQuery->count();
            }
        } else {
            // Inclure tous les salaires payés de l'année active dans les statistiques
            $salaires = $salairesQuery->get();
            $totalSalaires = $salaires->sum('salaire_net');
            $nombreSalaires = $salaires->count();
        }
        
        $total = $totalDepenses + ($totalSalaires ?? 0);
        $nombre = $nombreDepenses + ($nombreSalaires ?? 0);
        $moyenne = $nombre > 0 ? ($total / $nombre) : 0;
        
        return [
            'total' => $total,
            'nombre' => $nombre,
            'moyenne' => $moyenne
        ];
    }

    /**
     * Générer le rapport journalier
     */
    public function rapportJournalier(Request $request)
    {
        $type = $request->get('type', 'jour');
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $year = $request->get('year', Carbon::now()->year);
        
        // Déterminer la période selon le type
        switch($type) {
            case 'mois':
                $dateDebut = Carbon::parse($month . '-01');
                $dateFin = $dateDebut->copy()->endOfMonth();
                $dateCarbon = $dateDebut;
                break;
            case 'annee':
                $dateDebut = Carbon::create($year, 1, 1);
                $dateFin = Carbon::create($year, 12, 31);
                $dateCarbon = $dateDebut;
                break;
            default: // jour
                $dateDebut = Carbon::parse($date);
                $dateFin = $dateDebut->copy();
                $dateCarbon = $dateDebut;
                break;
        }
        
        // Récupérer l'année scolaire active
        $anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();
        
        // Récupérer les entrées selon la période
        $entrees = Entree::with('enregistrePar')
            ->whereBetween('date_entree', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Récupérer les paiements selon la période
        $paiements = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
            ->whereBetween('date_paiement', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Récupérer les dépenses selon la période
        $depenses = Depense::with(['approuvePar', 'payePar'])
            ->whereBetween('date_depense', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Créer le journal des transactions
        $journal = collect();
        
        // Ajouter les entrées manuelles
        foreach ($entrees as $entree) {
            $journal->push([
                'date' => $entree->date_entree,
                'libelle' => $entree->description,
                'entree' => $entree->montant,
                'sortie' => 0,
                'type' => 'entree_manuelle',
                'source' => $entree->source,
                'enregistre_par' => $entree->enregistrePar,
                'created_at' => $entree->created_at
            ]);
        }
        
        // Ajouter les paiements de frais de scolarité
        foreach ($paiements as $paiement) {
            $journal->push([
                'date' => $paiement->date_paiement,
                'libelle' => 'Paiement frais scolarité - ' . $paiement->fraisScolarite->eleve->utilisateur->prenom . ' ' . $paiement->fraisScolarite->eleve->utilisateur->nom,
                'entree' => $paiement->montant_paye,
                'sortie' => 0,
                'type' => 'paiement_scolarite',
                'source' => 'Frais de scolarité',
                'enregistre_par' => $paiement->encaissePar,
                'created_at' => $paiement->created_at
            ]);
        }
        
        // Ajouter les dépenses
        foreach ($depenses as $depense) {
            $journal->push([
                'date' => $depense->date_depense,
                'libelle' => $depense->libelle,
                'entree' => 0,
                'sortie' => $depense->montant,
                'type' => 'depense',
                'source' => $depense->type_depense,
                'enregistre_par' => $depense->approuvePar ?? $depense->payePar,
                'created_at' => $depense->created_at
            ]);
        }
        
        // Trier par heure de création (plus récent en premier)
        $journal = $journal->sortByDesc('created_at');
        
        // Calculer le solde cumulé (comme dans l'exemple)
        $soldeInitial = $this->getSoldeInitial($dateDebut->format('Y-m-d'));
        $soldeActuel = $soldeInitial;
        
        $journal = $journal->map(function($transaction) use (&$soldeActuel) {
            $soldeActuel += $transaction['entree'] - $transaction['sortie'];
            $transaction['solde'] = $soldeActuel;
            return $transaction;
        });
        
        // Statistiques de la période
        $totalEntrees = $journal->sum('entree');
        $totalSorties = $journal->sum('sortie');
        $soldeFinal = $soldeActuel;
        
        return view('comptabilite.rapport-journalier', compact(
            'journal',
            'date',
            'dateCarbon',
            'soldeInitial',
            'totalEntrees',
            'totalSorties',
            'soldeFinal'
        ));
    }
    
    /**
     * Calculer le solde initial avant la date donnée
     */
    private function getSoldeInitial($date)
    {
        $dateCarbon = Carbon::parse($date);
        
        // Calculer le solde des entrées avant cette date
        $entreesAvant = Entree::whereDate('date_entree', '<', $dateCarbon)
            ->sum('montant');
        
        $paiementsAvant = Paiement::whereDate('date_paiement', '<', $dateCarbon)
            ->sum('montant_paye');
        
        $depensesAvant = Depense::whereDate('date_depense', '<', $dateCarbon)
            ->sum('montant');
        
        return $entreesAvant + $paiementsAvant - $depensesAvant;
    }
}
