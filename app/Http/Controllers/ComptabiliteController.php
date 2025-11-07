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
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Statistiques générales pour l'année active
        $stats = $this->getComptabiliteStats($anneeScolaireActive);
        
        // Récupérer toutes les entrées manuelles de l'année scolaire active
        $entreesManuelles = Entree::with('enregistrePar')
            ->whereBetween('date_entree', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ])
            ->orderBy('date_entree', 'desc')
            ->get();
        
        // Récupérer tous les paiements de frais de scolarité de l'année active
        $paiementsFrais = Paiement::with(['fraisScolarite.eleve.utilisateur', 'fraisScolarite.eleve.classe', 'fraisScolarite:id,type_frais,eleve_id', 'encaissePar'])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->orderBy('date_paiement', 'desc')
            ->get();
        
        // Exclure les entrées automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        $paiementsReferences = $paiementsFrais->pluck('reference_paiement')->filter()->toArray();
        
        // Combiner toutes les entrées (entrées manuelles + paiements)
        $toutesLesEntrees = collect();
        
        // Ajouter les entrées manuelles (en excluant celles qui correspondent à un paiement)
        foreach ($entreesManuelles as $entree) {
            // Vérifier si cette entrée correspond à un paiement (pour éviter les doublons)
            $isPaiementEntry = false;
            
            // Vérifier par référence
            if ($entree->reference && in_array($entree->reference, $paiementsReferences)) {
                $isPaiementEntry = true;
            }
            
            // Vérifier aussi par montant, date et source pour les entrées de scolarité
            if (!$isPaiementEntry && in_array($entree->source, $sourcesAuto)) {
                foreach ($paiementsFrais as $paiement) {
                    if ($paiement->montant_paye == $entree->montant && 
                        $paiement->date_paiement->format('Y-m-d') == $entree->date_entree->format('Y-m-d') &&
                        $paiement->encaisse_par == $entree->enregistre_par) {
                        $isPaiementEntry = true;
                        break;
                    }
                }
            }
            
            // Si c'est une entrée créée automatiquement par un paiement, on l'exclut
            if ($isPaiementEntry) {
                continue;
            }
            
            $toutesLesEntrees->push((object) [
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
        
        // Ajouter tous les paiements
        foreach ($paiementsFrais as $paiement) {
            $eleve = $paiement->fraisScolarite->eleve;
            $eleveNom = $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom;
            $matricule = $eleve->matricule ?? 'N/A';
            $classe = $eleve->classe->nom ?? 'N/A';
            $source = $getSourceFromTypeFrais($paiement->fraisScolarite->type_frais);
            $description = 'Paiement de ' . number_format($paiement->montant_paye, 0, ',', ' ') . ' GNF - ' . $eleveNom . ' (Mat: ' . $matricule . ', Classe: ' . $classe . ')';
            
            $toutesLesEntrees->push((object) [
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
        $toutesLesEntrees = $toutesLesEntrees->sortByDesc('date');
            
        // Récupérer toutes les dépenses de l'année scolaire active
        $depenses = Depense::with(['approuvePar', 'payePar'])
            ->whereBetween('date_depense', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ])
            ->orderBy('date_depense', 'desc')
            ->get();
        
        // Récupérer tous les salaires enseignants payés de l'année active
        $salairesPayes = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ])
            ->with(['enseignant.utilisateur', 'payePar'])
            ->orderBy('date_paiement', 'desc')
            ->get();
        
        // Combiner toutes les sorties (dépenses + salaires enseignants)
        $toutesLesSorties = collect();
        
        // Ajouter les dépenses
        foreach ($depenses as $depense) {
            // Vérifier si cette dépense correspond à un salaire (pour éviter les doublons)
            $correspondSalaire = false;
            foreach ($salairesPayes as $salaire) {
                if ($depense->type_depense == 'salaire_enseignant' &&
                    $depense->montant == $salaire->salaire_net &&
                    $depense->date_depense->format('Y-m-d') == $salaire->date_paiement->format('Y-m-d')) {
                    $correspondSalaire = true;
                    break;
                }
            }
            
            // Si c'est une dépense qui correspond à un salaire, on l'exclut
            if ($correspondSalaire) {
                continue;
            }
            
            $toutesLesSorties->push((object) [
                'id' => 'depense_' . $depense->id,
                'type' => 'depense',
                'date' => $depense->date_depense,
                'description' => $depense->libelle,
                'montant' => $depense->montant,
                'type_depense' => $depense->type_depense,
                'enregistre_par' => $depense->approuvePar,
                'data' => $depense
            ]);
        }
        
        // Ajouter tous les salaires enseignants
        foreach ($salairesPayes as $salaire) {
            $enseignantNom = $salaire->enseignant->utilisateur->nom . ' ' . $salaire->enseignant->utilisateur->prenom;
            
            // Vérifier que les dates ne sont pas null avant de les formater
            $dateDebut = $salaire->date_debut ? $salaire->date_debut->format('d/m/Y') : 'N/A';
            $dateFin = $salaire->date_fin ? $salaire->date_fin->format('d/m/Y') : 'N/A';
            $description = 'Salaire ' . $enseignantNom . ' - ' . $dateDebut . ' - ' . $dateFin;
            
            $toutesLesSorties->push((object) [
                'id' => 'salaire_' . $salaire->id,
                'type' => 'salaire',
                'date' => $salaire->date_paiement,
                'description' => $description,
                'montant' => $salaire->salaire_net,
                'type_depense' => 'salaire_enseignant',
                'enregistre_par' => $salaire->payePar,
                'data' => $salaire
            ]);
        }
        
        // Trier par date décroissante
        $toutesLesSorties = $toutesLesSorties->sortByDesc('date');
        
        // Calculer les totaux à partir des collections (pour correspondre exactement aux tableaux)
        $totalRevenus = $toutesLesEntrees->sum('montant');
        $totalSorties = $toutesLesSorties->sum('montant');
        $beneficeTotal = $totalRevenus - $totalSorties;
        
        // Mettre à jour les stats avec les totaux calculés
        $stats['revenus_total'] = $totalRevenus;
        $stats['depenses_total'] = $totalSorties;
        $stats['benefice_total'] = $beneficeTotal;
        
        return view('comptabilite.index', compact('stats', 'toutesLesEntrees', 'toutesLesSorties', 'anneeScolaireActive', 'totalRevenus', 'totalSorties', 'beneficeTotal'));
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
        
        // Récupérer les entrées manuelles
        $query = Entree::with('enregistrePar');
        
        // Filtrer par période de l'année scolaire sélectionnée
        if ($anneeScolaire) {
            $query->whereBetween('date_entree', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
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
        
        // Filtre par montant minimum
        if ($request->filled('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        }
        
        // Filtre par montant maximum
        if ($request->filled('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }
        
        // Filtre par type d'entrée
        if ($request->filled('type_entree')) {
            if ($request->type_entree == 'manuelle') {
                // Ne rien faire, on garde les entrées manuelles
            } elseif ($request->type_entree == 'paiement') {
                // On ne récupérera que les paiements plus tard
                $query->whereRaw('1 = 0'); // Ne récupérer aucune entrée manuelle
            }
        }
        
        $entrees = $query->orderBy('date_entree', 'desc')->get();

        // Récupérer les paiements de frais de scolarité de l'année sélectionnée
        $paiementsFraisQuery = Paiement::with(['fraisScolarite.eleve.utilisateur', 'fraisScolarite.eleve.classe', 'fraisScolarite:id,type_frais,eleve_id', 'encaissePar'])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
            });
        
        // Appliquer les filtres de date aux paiements
        if ($request->filled('date_debut')) {
            $paiementsFraisQuery->whereDate('date_paiement', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $paiementsFraisQuery->whereDate('date_paiement', '<=', $request->date_fin);
        }
        
        // Filtre par montant minimum pour les paiements
        if ($request->filled('montant_min')) {
            $paiementsFraisQuery->where('montant_paye', '>=', $request->montant_min);
        }
        
        // Filtre par montant maximum pour les paiements
        if ($request->filled('montant_max')) {
            $paiementsFraisQuery->where('montant_paye', '<=', $request->montant_max);
        }
        
        $paiementsFrais = $paiementsFraisQuery->orderBy('date_paiement', 'desc')->get();

        // Créer une collection des références de paiements pour éviter les doublons
        $paiementsReferences = $paiementsFrais->pluck('reference_paiement')->filter()->toArray();
        
        // Combiner les deux collections et créer une pagination unifiée
        $allEntries = collect();
        
        // Ajouter les entrées manuelles avec un type (en excluant celles qui correspondent à un paiement)
        foreach ($entrees as $entree) {
            // Vérifier si cette entrée correspond à un paiement (pour éviter les doublons)
            // Si l'entrée a une référence qui correspond à un paiement, on l'exclut
            $isPaiementEntry = false;
            
            // Vérifier par référence
            if ($entree->reference && in_array($entree->reference, $paiementsReferences)) {
                $isPaiementEntry = true;
            }
            
            // Vérifier aussi par montant, date et source pour les entrées de scolarité
            if (!$isPaiementEntry && in_array($entree->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'])) {
                // Vérifier si un paiement correspond (même montant, même date)
                foreach ($paiementsFrais as $paiement) {
                    if ($paiement->montant_paye == $entree->montant && 
                        $paiement->date_paiement->format('Y-m-d') == $entree->date_entree->format('Y-m-d') &&
                        $paiement->encaisse_par == $entree->enregistre_par) {
                        $isPaiementEntry = true;
                        break;
                    }
                }
            }
            
            // Si c'est une entrée créée automatiquement par un paiement, on l'exclut
            if ($isPaiementEntry) {
                continue;
            }
            
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
        
        // Ajouter tous les paiements de frais de scolarité (déjà filtrés)
        foreach ($paiementsFrais as $paiement) {
            // Récupérer l'élève pour la description
            $eleve = $paiement->fraisScolarite->eleve ?? null;
            $eleveNom = $eleve && $eleve->utilisateur ? 
                ($eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom) : 
                'Élève inconnu';
            
            // Récupérer le matricule et la classe
            $matricule = $eleve ? ($eleve->numero_etudiant ?? 'N/A') : 'N/A';
            $classe = $eleve && $eleve->classe ? $eleve->classe->nom : 'N/A';
            
            // Créer la description avec matricule et classe
            $description = 'Paiement de ' . number_format($paiement->montant_paye, 0, ',', ' ') . ' GNF - ' . $eleveNom . ' (Mat: ' . $matricule . ', Classe: ' . $classe . ')';
            
            // Utiliser le type de frais comme source
            $typeFrais = $paiement->fraisScolarite->type_frais ?? 'autre';
            $source = $getSourceFromTypeFrais($typeFrais);
            
            // Appliquer le filtre de source si spécifié
            if ($request->filled('source') && $source !== $request->source) {
                continue; // Ignorer ce paiement s'il ne correspond pas au filtre
            }
            
            // Appliquer le filtre de type d'entrée
            if ($request->filled('type_entree') && $request->type_entree == 'manuelle') {
                continue; // Ignorer les paiements si on veut seulement les entrées manuelles
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
        
        // Appliquer le filtre de type d'entrée pour les entrées manuelles
        if ($request->filled('type_entree') && $request->type_entree == 'paiement') {
            // On a déjà filtré les entrées manuelles plus haut
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
        
        // Statistiques des entrées (filtrées par année scolaire sélectionnée)
        $statsEntrees = $this->getStatsEntrees($request, $anneeScolaire);
        
        // Sources disponibles pour les filtres basées sur les types de frais des paiements
        $sources = [];
        
        // Récupérer les types de frais uniques des paiements de l'année sélectionnée
        $typesFraisQuery = \App\Models\Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaire) {
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
        
        // Trier par date de dépense décroissante (plus récentes en premier)
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
        $salairesQuery = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [
                $anneeScolaireActive->date_debut->format('Y-m-d'),
                $anneeScolaireActive->date_fin->format('Y-m-d')
            ]);
        
        // Appliquer les filtres de date supplémentaires (comme pour les dépenses)
        if ($request->filled('date_debut')) {
            $salairesQuery->whereDate('date_paiement', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $salairesQuery->whereDate('date_paiement', '<=', $request->date_fin);
        }
        
        // Filtrer par type si spécifié
        if ($request->filled('type_depense') && $request->type_depense !== 'salaire_enseignant') {
            // Si le filtre n'est pas pour salaire_enseignant, ne pas inclure les salaires
            $salairesPayes = collect();
        } else {
            // Trier par date de paiement décroissante (plus récentes en premier)
            $salairesPayes = $salairesQuery->with(['enseignant.utilisateur', 'payePar'])
                ->orderBy('date_paiement', 'desc')
                ->get();
        }
        
        // Combiner les deux collections et créer une pagination unifiée
        $allSorties = collect();
        
        // Ajouter les dépenses avec un type (en excluant celles qui correspondent à un salaire)
        foreach ($depenses as $depense) {
            // Vérifier si cette dépense correspond à un salaire (pour éviter les doublons)
            $correspondSalaire = false;
            foreach ($salairesPayes as $salaire) {
                if ($depense->type_depense === 'salaire_enseignant' && 
                    $depense->date_depense->format('Y-m-d') == $salaire->date_paiement->format('Y-m-d') &&
                    abs($depense->montant - $salaire->salaire_net) < 0.01) {
                    $correspondSalaire = true;
                    break;
                }
            }
            
            // Si c'est une dépense de salaire déjà représentée par un salaire payé, on la saute pour éviter les doublons
            if ($correspondSalaire) {
                continue;
            }
            
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
        
        // Ajouter TOUS les salaires d'enseignants payés (déjà filtrés par date dans la requête)
        foreach ($salairesPayes as $salaire) {
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
        
        // Trier par date de sortie décroissante (plus récentes en haut)
        // date_depense pour les dépenses, date_paiement pour les salaires
        $allSorties = $allSorties->sortByDesc(function($item) {
            // S'assurer que la date est bien un objet Carbon pour le tri
            if ($item->date instanceof \Carbon\Carbon) {
                return $item->date->timestamp;
            }
            // Si c'est une chaîne, la convertir en timestamp
            if (is_string($item->date)) {
                return strtotime($item->date);
            }
            // Si null, retourner 0 pour mettre en fin
            return 0;
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
        
        // Exclure toutes les sources automatiques créées par les paiements (pour éviter les doublons)
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        
        // Revenus du mois actuel (entrées manuelles) - filtrer par période de l'année scolaire
        $revenusMois = 0;
        if ($anneeScolaireActive) {
            // Récupérer les références des paiements du mois pour exclure les entrées correspondantes
            $paiementsMoisQuery = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->whereMonth('date_paiement', $moisActuel->month)
            ->whereYear('date_paiement', $moisActuel->year);
            
            $paiementsReferencesMois = $paiementsMoisQuery->pluck('reference_paiement')->filter()->toArray();
            
            $revenusMois = Entree::whereMonth('date_entree', $moisActuel->month)
                ->whereYear('date_entree', $moisActuel->year)
                ->whereBetween('date_entree', [
                    $anneeScolaireActive->date_debut,
                    $anneeScolaireActive->date_fin
                ])
                ->whereNotIn('source', $sourcesAuto);
            
            // Exclure aussi les entrées avec une référence de paiement
            if (!empty($paiementsReferencesMois)) {
                $revenusMois->whereNotIn('reference', $paiementsReferencesMois);
            }
            
            $revenusMois = $revenusMois->sum('montant');
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
            // Récupérer les références des paiements pour exclure les entrées correspondantes
            $paiementsTotalQuery = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
            
            $paiementsReferencesTotal = $paiementsTotalQuery->pluck('reference_paiement')->filter()->toArray();
            
            $revenusTotal = Entree::whereBetween('date_entree', [
                $anneeScolaireActive->date_debut,
                $anneeScolaireActive->date_fin
            ])
            ->whereNotIn('source', $sourcesAuto);
            
            // Exclure aussi les entrées avec une référence de paiement
            if (!empty($paiementsReferencesTotal)) {
                $revenusTotal->whereNotIn('reference', $paiementsReferencesTotal);
            }
            
            $revenusTotal = $revenusTotal->sum('montant');
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
            
        // Total des revenus (entrées manuelles + paiements) - utiliser exactement la même logique que entrees()
        // Filtrer d'abord par année scolaire (comme dans entrees)
        $totalEntreesManuellesQuery = Entree::query();
        
        if ($anneeScolaire) {
            $totalEntreesManuellesQuery->whereBetween('date_entree', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d')
            ]);
        }
        
        // Ensuite appliquer les filtres de date supplémentaires seulement si fournis (comme dans entrees)
        if ($request && ($request->filled('date_debut') || $request->filled('date_fin'))) {
            if ($request->filled('date_debut')) {
                $totalEntreesManuellesQuery->whereDate('date_entree', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $totalEntreesManuellesQuery->whereDate('date_entree', '<=', $request->date_fin);
            }
        }
        
        // Exclure les sources automatiques
        $totalEntreesManuellesQuery->whereNotIn('source', $sourcesAuto);
        
        // Exclure aussi les entrées avec une référence de paiement
        if (!empty($paiementsReferences)) {
            $totalEntreesManuellesQuery->whereNotIn('reference', $paiementsReferences);
        }
        
        $totalEntreesManuelles = $totalEntreesManuellesQuery->sum('montant');
        
        // Ajouter les paiements (déjà filtrés par année scolaire et dates)
        $totalPaiements = $paiementsQuery->sum('montant_paye');
        $totalRevenus = $totalEntreesManuelles + $totalPaiements;
            
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
        $query = Entree::query();
        
        // Filtrer par période de l'année scolaire sélectionnée
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
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        // Filtre par montant minimum
        if ($request->filled('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        }
        
        // Filtre par montant maximum
        if ($request->filled('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }
        
        // Filtre par type d'entrée
        if ($request->filled('type_entree') && $request->type_entree == 'paiement') {
            $query->whereRaw('1 = 0'); // Ne pas compter les entrées manuelles
        }
        
        // Récupérer les paiements pour exclure les entrées correspondantes (éviter les doublons)
        $paiementsFraisQuery = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
            if ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        });
        
        if ($request->filled('date_debut')) {
            $paiementsFraisQuery->whereDate('date_paiement', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $paiementsFraisQuery->whereDate('date_paiement', '<=', $request->date_fin);
        }
        
        $paiementsFrais = $paiementsFraisQuery->get();
        $paiementsReferences = $paiementsFrais->pluck('reference_paiement')->filter()->toArray();
        
        // Exclure les entrées qui correspondent à un paiement (pour éviter les doublons)
        // Les entrées créées automatiquement par les paiements ont la même référence
        if (!empty($paiementsReferences)) {
            $query->whereNotIn('reference', $paiementsReferences);
        }
        
        // Exclure aussi les entrées de scolarité qui correspondent à un paiement (même montant, même date)
        // mais seulement si elles n'ont pas de référence
        $entrees = $query->get();
        $entreesFiltrees = $entrees->filter(function($entree) use ($paiementsFrais) {
            // Si l'entrée a une source de scolarité, vérifier si elle correspond à un paiement
            if (in_array($entree->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'])) {
                foreach ($paiementsFrais as $paiement) {
                    if ($paiement->montant_paye == $entree->montant && 
                        $paiement->date_paiement->format('Y-m-d') == $entree->date_entree->format('Y-m-d') &&
                        $paiement->encaisse_par == $entree->enregistre_par) {
                        return false; // Exclure cette entrée car elle correspond à un paiement
                    }
                }
            }
            return true; // Garder cette entrée
        });
        
        // Calculer les statistiques des entrées manuelles (sans les doublons)
        $totalEntrees = $entreesFiltrees->sum('montant');
        $nombreEntrees = $entreesFiltrees->count();
        
        // Ajouter les paiements de frais de scolarité
        if ($anneeScolaireActive && (!$request->filled('type_entree') || $request->type_entree != 'manuelle')) {
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
            
            $paiementsQuery = Paiement::whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->with('fraisScolarite:id,type_frais');
            
            if ($request->filled('date_debut')) {
                $paiementsQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            
            if ($request->filled('date_fin')) {
                $paiementsQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
            
            // Filtre par montant minimum pour les paiements
            if ($request->filled('montant_min')) {
                $paiementsQuery->where('montant_paye', '>=', $request->montant_min);
            }
            
            // Filtre par montant maximum pour les paiements
            if ($request->filled('montant_max')) {
                $paiementsQuery->where('montant_paye', '<=', $request->montant_max);
            }
            
            $paiements = $paiementsQuery->get();
            
            // Filtre par source pour les paiements (basé sur le type de frais)
            if ($request->filled('source')) {
                $paiements = $paiements->filter(function($paiement) use ($request, $getSourceFromTypeFrais) {
                    $typeFrais = $paiement->fraisScolarite->type_frais ?? 'autre';
                    $source = $getSourceFromTypeFrais($typeFrais);
                    return $source === $request->source;
                });
            }
            
            $totalPaiements = $paiements->sum('montant_paye');
            $nombrePaiements = $paiements->count();
            
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
        
        // Exclure les dépenses de type salaire_enseignant pour éviter les doublons avec les salaires payés
        // (sauf si on filtre spécifiquement par salaire_enseignant)
        if (!$request->filled('type_depense') || $request->type_depense !== 'salaire_enseignant') {
            $query->where('type_depense', '!=', 'salaire_enseignant');
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
        
        // Récupérer les entrées selon la période (exclure celles créées automatiquement par les paiements)
        // Ces sources sont créées automatiquement lors des paiements et sont déjà représentées dans la section paiements
        $sourcesAuto = ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires'];
        $entrees = Entree::with('enregistrePar')
            ->whereBetween('date_entree', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->whereNotIn('source', $sourcesAuto)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Récupérer les paiements selon la période
        $paiements = Paiement::with(['fraisScolarite.eleve.utilisateur', 'fraisScolarite.eleve.classe', 'encaissePar'])
            ->whereBetween('date_paiement', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Récupérer les dépenses selon la période
        $depenses = Depense::with(['approuvePar', 'payePar'])
            ->whereBetween('date_depense', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Récupérer les salaires enseignants payés selon la période
        $salairesPayes = \App\Models\SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->with(['enseignant.utilisateur', 'payePar'])
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
            // Récupérer les informations de l'élève
            $eleve = $paiement->fraisScolarite->eleve ?? null;
            $eleveNom = $eleve && $eleve->utilisateur ? 
                ($eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom) : 
                'Élève inconnu';
            
            // Récupérer le matricule et la classe
            $matricule = $eleve ? ($eleve->numero_etudiant ?? 'N/A') : 'N/A';
            $classe = $eleve && $eleve->classe ? $eleve->classe->nom : 'N/A';
            
            // Créer le libellé avec matricule et classe
            $libelle = 'Paiement frais scolarité - ' . $eleveNom . ' (Mat: ' . $matricule . ', Classe: ' . $classe . ')';
            
            $journal->push([
                'date' => $paiement->date_paiement,
                'libelle' => $libelle,
                'entree' => $paiement->montant_paye,
                'sortie' => 0,
                'type' => 'paiement_scolarite',
                'source' => 'Frais de scolarité',
                'enregistre_par' => $paiement->encaissePar,
                'created_at' => $paiement->created_at
            ]);
        }
        
        // Ajouter les dépenses (en excluant celles qui correspondent à un salaire pour éviter les doublons)
        foreach ($depenses as $depense) {
            // Vérifier si cette dépense correspond à un salaire (pour éviter les doublons)
            $correspondSalaire = false;
            foreach ($salairesPayes as $salaire) {
                if ($depense->type_depense === 'salaire_enseignant' && 
                    $depense->date_depense->format('Y-m-d') == $salaire->date_paiement->format('Y-m-d') &&
                    abs($depense->montant - $salaire->salaire_net) < 0.01) {
                    $correspondSalaire = true;
                    break;
                }
            }
            
            // Si c'est une dépense de salaire déjà représentée par un salaire payé, on la saute
            if ($correspondSalaire) {
                continue;
            }
            
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
        
        // Ajouter les salaires enseignants payés
        foreach ($salairesPayes as $salaire) {
            $enseignantNom = $salaire->enseignant && $salaire->enseignant->utilisateur ? 
                ($salaire->enseignant->utilisateur->prenom . ' ' . $salaire->enseignant->utilisateur->nom) : 
                'Enseignant inconnu';
            
            $libelle = 'Salaire - ' . $enseignantNom . ' (' . ($salaire->periode_debut ? $salaire->periode_debut->format('d/m/Y') : 'N/A') . ' - ' . ($salaire->periode_fin ? $salaire->periode_fin->format('d/m/Y') : 'N/A') . ')';
            
            $journal->push([
                'date' => $salaire->date_paiement,
                'libelle' => $libelle,
                'entree' => 0,
                'sortie' => $salaire->salaire_net ?? 0,
                'type' => 'salaire_enseignant',
                'source' => 'Salaire enseignant',
                'enregistre_par' => $salaire->payePar,
                'created_at' => $salaire->created_at
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
        // Solde final = Entrées - Sorties
        $soldeFinal = $totalEntrees - $totalSorties;
        
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
