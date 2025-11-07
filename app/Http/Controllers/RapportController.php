<?php

namespace App\Http\Controllers;

use App\Models\Entree;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\SalaireEnseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    /**
     * Afficher le tableau de bord des rapports comptables
     */
    public function index(Request $request)
    {
        // Récupérer les filtres
        $dateDebut = $request->get('date_debut', now()->subMonths(6)->format('Y-m-01'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-t'));
        $typeDepense = $request->get('type_depense');
        $sourceEntree = $request->get('source_entree');
        $statutDepense = $request->get('statut_depense', 'paye'); // Par défaut, seulement les dépenses payées

        // Construire les requêtes avec filtres
        $entreesQuery = Entree::whereBetween('date_entree', [$dateDebut, $dateFin]);
        $sortiesQuery = Depense::whereBetween('date_depense', [$dateDebut, $dateFin]);
        $paiementsQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin]);

        // Appliquer les filtres spécifiques
        if ($sourceEntree) {
            $entreesQuery->where('source', $sourceEntree);
        }
        
        if ($typeDepense) {
            $sortiesQuery->where('type_depense', $typeDepense);
        }
        
        if ($statutDepense) {
            $sortiesQuery->where('statut', $statutDepense);
        }

        // Statistiques générales
        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Récupérer les références des paiements pour exclure les entrées correspondantes
        $paiementsReferences = $paiementsQuery->pluck('reference_paiement')->filter()->toArray();
        
        // Entrées manuelles (exclure les entrées créées automatiquement par les paiements)
        $entreesManuellesQuery = clone $entreesQuery;
        $entreesManuellesQuery->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesManuellesQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $totalEntreesManuelles = $entreesManuellesQuery->sum('montant');
        
        // Frais de scolarité (paiements scolaires) - tous les paiements
        $totalPaiements = $paiementsQuery->sum('montant_paye');
        
        // Total Entrées = Entrées manuelles + Frais de scolarité
        $totalEntrees = $totalEntreesManuelles + $totalPaiements;
        
        // Sorties manuelles (exclure les salaires enseignants)
        $totalSortiesManuelles = $sortiesQuery->where('type_depense', '!=', 'salaire_enseignant')->sum('montant');
        
        // Salaires enseignants (depuis la table salaires_enseignants) - avec filtres de dates
        $totalSalairesEnseignants = SalaireEnseignant::where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('salaire_net');
        
        // Total Sorties = Sorties manuelles + Salaires enseignants
        $totalSorties = $totalSortiesManuelles + $totalSalairesEnseignants;
        
        // Solde = Total Entrées - Total Sorties
        $solde = $totalEntrees - $totalSorties;

        // Entrées par mois (période filtrée) - inclure les paiements
        $entreesParMois = Entree::select(
                DB::raw('YEAR(date_entree) as annee'),
                DB::raw('MONTH(date_entree) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->whereBetween('date_entree', [$dateDebut, $dateFin])
            ->whereNotIn('source', $sourcesAuto);
            
        if ($sourceEntree) {
            $entreesParMois->where('source', $sourceEntree);
        }
        
        // Exclure les entrées avec référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesParMois->whereNotIn('reference', $paiementsReferences);
        }
        
        $entreesParMois = $entreesParMois->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();
        
        // Ajouter les paiements par mois
        $paiementsParMois = Paiement::select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(montant_paye) as total')
            )
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();
        
        // Combiner les entrées manuelles et les paiements par mois
        $entreesParMoisCombines = collect();
        foreach ($entreesParMois as $entree) {
            $key = $entree->annee . '-' . $entree->mois;
            $entreesParMoisCombines->put($key, [
                'annee' => $entree->annee,
                'mois' => $entree->mois,
                'total' => $entree->total
            ]);
        }
        
        foreach ($paiementsParMois as $paiement) {
            $key = $paiement->annee . '-' . $paiement->mois;
            if ($entreesParMoisCombines->has($key)) {
                $entreesParMoisCombines[$key]['total'] += $paiement->total;
            } else {
                $entreesParMoisCombines->put($key, [
                    'annee' => $paiement->annee,
                    'mois' => $paiement->mois,
                    'total' => $paiement->total
                ]);
            }
        }
        
        $entreesParMois = $entreesParMoisCombines->values()->sortByDesc(function($item) {
            return $item['annee'] * 100 + $item['mois'];
        })->values();

        // Sorties par mois (période filtrée)
        $sortiesParMois = Depense::select(
                DB::raw('YEAR(date_depense) as annee'),
                DB::raw('MONTH(date_depense) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->whereBetween('date_depense', [$dateDebut, $dateFin]);
            
        if ($typeDepense) {
            $sortiesParMois->where('type_depense', $typeDepense);
        }
        
        if ($statutDepense) {
            $sortiesParMois->where('statut', $statutDepense);
        }
        
        $sortiesParMois = $sortiesParMois->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        // Entrées par source (période filtrée) - exclure les sources automatiques
        $entreesParSourceQuery = Entree::select('source', DB::raw('SUM(montant) as total'))
            ->whereBetween('date_entree', [$dateDebut, $dateFin])
            ->whereNotIn('source', $sourcesAuto);
        
        // Exclure les entrées avec référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesParSourceQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $entreesParSource = $entreesParSourceQuery->groupBy('source')
            ->orderBy('total', 'desc')
            ->get();
        
        // Ajouter les paiements par type de frais
        $paiementsParTypeFrais = Paiement::select('frais_scolarite.type_frais', DB::raw('SUM(paiements.montant_paye) as total'))
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
            ->whereNotNull('frais_scolarite.type_frais')
            ->groupBy('frais_scolarite.type_frais')
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
        $entreesParSourceCombines = collect();
        foreach ($entreesParSource as $entree) {
            $entreesParSourceCombines->put($entree->source, [
                'source' => $entree->source,
                'total' => $entree->total
            ]);
        }
        
        foreach ($paiementsParTypeFrais as $paiement) {
            $source = $getSourceFromTypeFrais($paiement->type_frais);
            if ($entreesParSourceCombines->has($source)) {
                $entreesParSourceCombines[$source]['total'] += $paiement->total;
            } else {
                $entreesParSourceCombines->put($source, [
                    'source' => $source,
                    'total' => $paiement->total
                ]);
            }
        }
        
        $entreesParSource = $entreesParSourceCombines->values()->sortByDesc('total')->values();

        // Sorties par type (période filtrée)
        $sortiesParType = Depense::select('type_depense', DB::raw('SUM(montant) as total'))
            ->whereBetween('date_depense', [$dateDebut, $dateFin]);
            
        if ($statutDepense) {
            $sortiesParType->where('statut', $statutDepense);
        }
        
        $sortiesParType = $sortiesParType->groupBy('type_depense')
            ->orderBy('total', 'desc')
            ->get();

        // Récupérer les options pour les filtres
        $typesDepense = Depense::select('type_depense')
            ->distinct()
            ->whereNotNull('type_depense')
            ->orderBy('type_depense')
            ->pluck('type_depense');
            
        $sourcesEntree = Entree::select('source')
            ->distinct()
            ->whereNotNull('source')
            ->orderBy('source')
            ->pluck('source');

        return view('rapports.index', compact(
            'totalEntrees',
            'totalEntreesManuelles',
            'totalSorties', 
            'totalSortiesManuelles',
            'totalPaiements',
            'totalSalairesEnseignants',
            'solde',
            'entreesParMois',
            'sortiesParMois',
            'entreesParSource',
            'sortiesParType',
            'dateDebut',
            'dateFin',
            'typeDepense',
            'sourceEntree',
            'statutDepense',
            'typesDepense',
            'sourcesEntree'
        ));
    }

    /**
     * Générer un rapport détaillé
     */
    public function detaille(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type' => 'required|in:entrees,sorties,tous',
            'type_depense' => 'nullable|string',
            'source_entree' => 'nullable|string',
            'statut_depense' => 'nullable|in:en_attente,approuve,paye,annule',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0|gte:montant_min'
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;
        $type = $request->type;
        $typeDepense = $request->type_depense;
        $sourceEntree = $request->source_entree;
        $statutDepense = $request->statut_depense;
        $montantMin = $request->montant_min;
        $montantMax = $request->montant_max;

        $entrees = collect();
        $sorties = collect();

        if ($type === 'entrees' || $type === 'tous') {
            $entreesQuery = Entree::with('enregistrePar')
                ->whereBetween('date_entree', [$dateDebut, $dateFin]);
            
            // Filtre par source d'entrée
            if ($sourceEntree) {
                $entreesQuery->where('source', $sourceEntree);
            }
            
            // Filtre par montant
            if ($montantMin) {
                $entreesQuery->where('montant', '>=', $montantMin);
            }
            if ($montantMax) {
                $entreesQuery->where('montant', '<=', $montantMax);
            }
            
            $entrees = $entreesQuery->orderBy('date_entree', 'desc')->get();
        }

        if ($type === 'sorties' || $type === 'tous') {
            $sortiesQuery = Depense::with(['approuvePar', 'payePar'])
                ->whereBetween('date_depense', [$dateDebut, $dateFin]);
            
            // Filtre par type de dépense
            if ($typeDepense) {
                $sortiesQuery->where('type_depense', $typeDepense);
            }
            
            // Filtre par statut
            if ($statutDepense) {
                $sortiesQuery->where('statut', $statutDepense);
            } else {
                // Par défaut, inclure seulement les dépenses payées
                $sortiesQuery->where('statut', 'paye');
            }
            
            // Filtre par montant
            if ($montantMin) {
                $sortiesQuery->where('montant', '>=', $montantMin);
            }
            if ($montantMax) {
                $sortiesQuery->where('montant', '<=', $montantMax);
            }
            
            $sorties = $sortiesQuery->orderBy('date_depense', 'desc')->get();
        }

        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Récupérer les références des paiements pour exclure les entrées correspondantes
        $paiementsQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin]);
        $paiementsReferences = $paiementsQuery->pluck('reference_paiement')->filter()->toArray();
        
        // Entrées manuelles (exclure les entrées créées automatiquement par les paiements)
        $entreesManuellesQuery = clone $entreesQuery;
        $entreesManuellesQuery->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesManuellesQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $totalEntreesManuelles = $entreesManuellesQuery->sum('montant');
        
        // Frais de scolarité (paiements scolaires) - tous les paiements
        $totalPaiements = $paiementsQuery->sum('montant_paye');
        
        // Total Entrées = Entrées manuelles + Frais de scolarité
        $totalEntrees = $totalEntreesManuelles + $totalPaiements;
        
        // Sorties manuelles (exclure les salaires enseignants)
        $totalSortiesManuelles = $sortiesQuery->where('type_depense', '!=', 'salaire_enseignant')->sum('montant');
        
        // Salaires enseignants (depuis la table salaires_enseignants) - avec filtres de dates
        $totalSalairesEnseignants = SalaireEnseignant::where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('salaire_net');
        
        // Total Sorties = Sorties manuelles + Salaires enseignants
        $totalSorties = $totalSortiesManuelles + $totalSalairesEnseignants;
        
        // Solde = Total Entrées - Total Sorties
        $solde = $totalEntrees - $totalSorties;

        // Récupérer les options pour les filtres
        $typesDepense = Depense::select('type_depense')
            ->distinct()
            ->whereNotNull('type_depense')
            ->orderBy('type_depense')
            ->pluck('type_depense');
            
        $sourcesEntree = Entree::select('source')
            ->distinct()
            ->whereNotNull('source')
            ->orderBy('source')
            ->pluck('source');

        return view('rapports.detaille', compact(
            'entrees',
            'sorties',
            'totalEntrees',
            'totalEntreesManuelles',
            'totalSorties',
            'totalSortiesManuelles',
            'totalPaiements',
            'totalSalairesEnseignants',
            'solde',
            'dateDebut',
            'dateFin',
            'type',
            'typeDepense',
            'sourceEntree',
            'statutDepense',
            'montantMin',
            'montantMax',
            'typesDepense',
            'sourcesEntree'
        ));
    }

    /**
     * Afficher tous les rapports unifiés dans une seule page
     */
    public function unifies(Request $request)
    {
        // Récupérer les filtres
        $dateDebut = $request->get('date_debut', now()->subMonths(6)->format('Y-m-01'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-t'));

        // === RAPPORTS FINANCIERS ===
        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Récupérer les références des paiements pour exclure les entrées correspondantes
        $paiementsQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin]);
        $paiementsReferences = $paiementsQuery->pluck('reference_paiement')->filter()->toArray();
        
        // Entrées manuelles (exclure les entrées créées automatiquement par les paiements)
        $entreesManuellesQuery = Entree::whereBetween('date_entree', [$dateDebut, $dateFin])
            ->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $entreesManuellesQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $totalEntreesManuelles = $entreesManuellesQuery->sum('montant');
        
        // Frais de scolarité (paiements scolaires) - tous les paiements
        $totalPaiements = $paiementsQuery->sum('montant_paye');
        
        // Total Entrées = Entrées manuelles + Frais de scolarité
        $totalEntrees = $totalEntreesManuelles + $totalPaiements;
        
        // Sorties manuelles (exclure les salaires enseignants)
        $totalSortiesManuelles = Depense::whereBetween('date_depense', [$dateDebut, $dateFin])
            ->where('type_depense', '!=', 'salaire_enseignant')
            ->sum('montant');
        
        // Salaires enseignants
        $totalSalairesEnseignants = SalaireEnseignant::where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('salaire_net');
        
        // Total Sorties = Sorties manuelles + Salaires enseignants
        $totalSorties = $totalSortiesManuelles + $totalSalairesEnseignants;
        
        // Solde = Total Entrées - Total Sorties
        $solde = $totalEntrees - $totalSorties;

        // === RAPPORTS DÉPENSES ===
        $depensesParType = Depense::select('type_depense', DB::raw('SUM(montant) as total'))
            ->whereBetween('date_depense', [$dateDebut, $dateFin])
            ->where('statut', 'paye')
            ->groupBy('type_depense')
            ->orderBy('total', 'desc')
            ->get();

        $depensesParMois = Depense::select(
                DB::raw('YEAR(date_depense) as annee'),
                DB::raw('MONTH(date_depense) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->whereBetween('date_depense', [$dateDebut, $dateFin])
            ->where('statut', 'paye')
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        // === RAPPORTS PAIEMENTS ===
        $paiementsParClasse = Paiement::select('classes.nom as classe', DB::raw('SUM(montant_paye) as total'))
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->join('eleves', 'frais_scolarite.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('classes.nom')
            ->orderBy('total', 'desc')
            ->get();

        $paiementsParMois = Paiement::select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(montant_paye) as total')
            )
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        // === RAPPORTS SALAIRES ===
        $salairesParEnseignant = SalaireEnseignant::select(
                'utilisateurs.nom',
                'utilisateurs.prenom',
                DB::raw('SUM(salaire_net) as total')
            )
            ->join('enseignants', 'salaires_enseignants.enseignant_id', '=', 'enseignants.id')
            ->join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->where('salaires_enseignants.statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('utilisateurs.nom', 'utilisateurs.prenom')
            ->orderBy('total', 'desc')
            ->get();

        $salairesParMois = SalaireEnseignant::select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(salaire_net) as total')
            )
            ->where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->get();

        return view('rapports.unifies', compact(
            // Rapports financiers
            'totalEntrees',
            'totalEntreesManuelles',
            'totalSorties',
            'totalSortiesManuelles',
            'totalPaiements',
            'totalSalairesEnseignants',
            'solde',
            // Rapports dépenses
            'depensesParType',
            'depensesParMois',
            // Rapports paiements
            'paiementsParClasse',
            'paiementsParMois',
            // Rapports salaires
            'salairesParEnseignant',
            'salairesParMois',
            // Filtres
            'dateDebut',
            'dateFin'
        ));
    }
}
