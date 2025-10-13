<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entree;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use App\Models\Eleve;
use App\Models\Classe;
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
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
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
        // Récupérer l'année scolaire active (toujours filtrer par l'année active)
        $anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();
        
        // Récupérer les entrées manuelles
        $query = Entree::with('enregistrePar');
        
        // Filtres
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

        // Récupérer les paiements de frais de scolarité de l'année sélectionnée seulement
        $paiementsFrais = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $q->where('annee_scolaire_id', $anneeScolaire->id);
                }
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
        
        // Ajouter les paiements de frais de scolarité avec un type
        // MAIS seulement s'ils n'ont pas déjà d'entrée comptable correspondante
        foreach ($paiementsFrais as $paiement) {
            // Récupérer l'entrée comptable correspondante (plus flexible)
            $entreeComptable = Entree::whereIn('source', ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'])
                ->where('montant', $paiement->montant_paye)
                ->where('date_entree', $paiement->date_paiement)
                ->where('enregistre_par', $paiement->encaisse_par)
                ->first();
            
            // Si pas trouvé par les critères stricts, essayer par référence
            if (!$entreeComptable && $paiement->reference_paiement) {
                $entreeComptable = Entree::where('reference', $paiement->reference_paiement)
                    ->whereIn('source', ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'])
                    ->where('montant', $paiement->montant_paye)
                    ->first();
            }
            
            // Si une entrée comptable existe déjà, ne pas ajouter le paiement pour éviter les doublons
            if ($entreeComptable) {
                continue;
            }
            
            $description = 'Paiement de ' . number_format($paiement->montant_paye, 0, ',', ' ') . ' GNF pour les frais de scolarité';
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
        
        // Statistiques des entrées
        $statsEntrees = $this->getStatsEntrees($request);
        
        // Sources disponibles pour les filtres
        $sources = Entree::select('source')->distinct()->orderBy('source')->pluck('source');
        
        return view('comptabilite.entrees', compact('paginatedEntries', 'statsEntrees', 'sources'));
    }

    /**
     * Afficher les sorties (dépenses)
     */
    public function sorties(Request $request)
    {
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        $query = Depense::with(['approuvePar', 'payePar']);
        
        // Filtrer par période de l'année scolaire active
        if ($anneeScolaireActive) {
            $query->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut,
                $anneeScolaireActive->date_fin
            ]);
        }
        
        // Filtres
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }
        
        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }
        
        $sorties = $query->orderBy('date_depense', 'desc')->paginate(20);
        
        // Statistiques des sorties
        $statsSorties = $this->getStatsSorties($request, $anneeScolaireActive);
        
        return view('comptabilite.sorties', compact('sorties', 'statsSorties'));
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
    private function getStatsEntrees($request)
    {
        $query = Entree::query();
        
        if ($request->filled('date_debut')) {
            $query->whereDate('date_entree', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_entree', '<=', $request->date_fin);
        }
        
        return [
            'total' => $query->sum('montant'),
            'nombre' => $query->count(),
            'moyenne' => $query->avg('montant')
        ];
    }

    /**
     * Obtenir les statistiques des sorties
     */
    private function getStatsSorties($request, $anneeScolaireActive = null)
    {
        $query = Depense::query();
        
        // Filtrer par période de l'année scolaire active
        if ($anneeScolaireActive) {
            $query->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut,
                $anneeScolaireActive->date_fin
            ]);
        }
        
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }
        
        return [
            'total' => $query->sum('montant'),
            'nombre' => $query->count(),
            'moyenne' => $query->avg('montant')
        ];
    }
}
