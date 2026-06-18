<?php

namespace App\Http\Controllers;

use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\Eleve;
use App\Models\Entree;
use App\Models\TarifClasse;
use App\Services\PaiementScolariteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    public function __construct(
        private PaiementScolariteService $paiementScolariteService
    ) {}
    /**
     * Annuler le dernier paiement d'un frais de scolarité
     */
    public function annulerDernierPaiement(Request $request, FraisScolarite $frais)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à annuler des paiements.');
        }

        try {
            DB::beginTransaction();

            // Récupérer le dernier paiement
            $dernierPaiement = $frais->paiements()
                ->orderBy('date_paiement', 'desc')
                ->first();

            if (!$dernierPaiement) {
                return redirect()->back()->with('error', 'Aucun paiement trouvé pour ce frais de scolarité.');
            }

            // Supprimer l'entrée comptable associée
            $entree = Entree::where('reference', $dernierPaiement->reference_paiement)
                ->where('montant', $dernierPaiement->montant_paye)
                ->where('date_entree', $dernierPaiement->date_paiement)
                ->where('enregistre_par', $dernierPaiement->encaisse_par)
                ->first();
            
            if ($entree) {
                $entree->delete();
            }

            // Si le paiement est lié à une tranche, remettre la tranche en attente
            if ($dernierPaiement->tranche_paiement_id) {
                $tranche = $dernierPaiement->tranchePaiement;
                if ($tranche) {
                    $tranche->statut = 'en_attente';
                    $tranche->montant_paye = 0;
                    $tranche->date_paiement = null;
                    $tranche->save();
                }
            }

            // Supprimer le paiement
            $dernierPaiement->delete();

            // Recalculer le montant restant (calcul dynamique)
            $montantPaye = $frais->paiements()->sum('montant_paye');
            $montantRestant = $frais->montant - $montantPaye;
            
            // Mettre à jour le statut
            if ($montantRestant <= 0) {
                $frais->statut = 'paye';
            } elseif ($frais->date_echeance < now()) {
                $frais->statut = 'en_retard';
            } else {
                $frais->statut = 'en_attente';
            }

            $frais->save();

            DB::commit();

            return redirect()->back()->with('success', 'Le dernier paiement a été annulé avec succès. L\'entrée comptable associée a également été supprimée.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erreur lors de l\'annulation du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher la liste des frais de scolarité
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $query = FraisScolarite::with(['eleve.utilisateur', 'eleve.classe', 'tranchesPaiement', 'paiements']);
        
        // Filtrer par année scolaire active (via les élèves)
        $query->whereHas('eleve', function($q) use ($anneeScolaireActive) {
            $q->where('annee_scolaire_id', $anneeScolaireActive->id);
        });
        
        // Filtre par classe
        if ($request->filled('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request, $anneeScolaireActive) {
                $q->where('classe_id', $request->classe_id)
                  ->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
        }
        
        // Filtre par matricule
        if ($request->filled('matricule')) {
            $query->whereHas('eleve', function($q) use ($request, $anneeScolaireActive) {
                $q->where('numero_etudiant', 'like', '%' . $request->matricule . '%')
                  ->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
        }
        
        // Filtre par nom de l'élève
        if ($request->filled('nom')) {
            $query->whereHas('eleve.utilisateur', function($q) use ($request) {
                $q->where(function($subQuery) use ($request) {
                    $subQuery->where('nom', 'like', '%' . $request->nom . '%')
                            ->orWhere('prenom', 'like', '%' . $request->nom . '%');
                });
            })->whereHas('eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
        }
        
        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $fraisScolarite = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('paiements.index', compact('fraisScolarite', 'anneeScolaireActive'));
    }

    /**
     * Afficher le formulaire de création de frais
     */
    public function create(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        // Récupérer les élèves éligibles: non exemptés ET sans frais de scolarité existants pour l'année en cours
        $eleves = Eleve::with(['utilisateur', 'classe'])
            ->where('exempte_frais', false)
            ->whereDoesntHave('fraisScolarite', function($q) {
                $q->where('type_frais', 'scolarite');
            })
            ->get()
            ->sortBy(function($eleve) {
                return $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom;
            });
            
        $selectedEleveId = $request->get('eleve_id');
        
        // Récupérer les tarifs des classes pour l'année scolaire active
        $tarifsClasses = TarifClasse::with('classe')
            ->where('actif', true)
            ->get()
            ->keyBy('classe_id');
        
        return view('paiements.create', compact('eleves', 'selectedEleveId', 'tarifsClasses'));
    }

    /**
     * Enregistrer un nouveau frais de scolarité
     */
public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_echeance' => 'required|date',
            'type_frais' => 'required|in:inscription,scolarite,cantine,transport,activites,autre',
            'type_paiement' => 'required|in:unique,tranches',
            'paiement_par_tranches' => 'boolean',
            'nombre_tranches' => 'required_if:type_paiement,tranches|nullable|integer|min:2|max:12',
            'periode_tranche' => 'required_if:type_paiement,tranches|nullable|in:mensuel,trimestriel,semestriel,annuel',
            'date_debut_tranches' => 'required_if:type_paiement,tranches|nullable|date'
        ]);

        // Vérifier que l'élève n'est pas exempté des frais de scolarité
        $eleve = Eleve::findOrFail($request->eleve_id);
        if ($eleve->exempte_frais) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de créer des frais de scolarité pour un élève exempté.');
        }

        // Vérifier qu'il n'existe pas déjà des frais de ce type pour cet élève
        $fraisExistants = FraisScolarite::where('eleve_id', $request->eleve_id)
            ->where('type_frais', $request->type_frais)
            ->count();
        
        if ($fraisExistants > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Des frais de {$request->type_frais} existent déjà pour cet élève.");
        }

        DB::beginTransaction();
        try {
            // Déterminer le type de paiement
            $paiementParTranches = $request->type_paiement === 'tranches';
            
            // Créer les données pour le frais
            $fraisData = $request->all();
            $fraisData['paiement_par_tranches'] = $paiementParTranches;
            
            $frais = FraisScolarite::create($fraisData);

            // Si paiement par tranches, créer les tranches
            if ($paiementParTranches) {
                $frais->creerTranchesPaiement();
            }

            DB::commit();
            return redirect()->route('paiements.show', $frais)
                ->with('success', 'Frais créé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un frais de scolarité
     */
    public function show(FraisScolarite $frais)
    {
        // Supprimer les tranches créées hors période puis répartir les paiements
        $this->nettoyerTranchesHorsPeriode($frais);
        $this->repartirPaiementsSurTranches($frais);
        
        $frais->load(['eleve.utilisateur', 'eleve.classe', 'tranchesPaiement', 'paiements.encaissePar']);
        return view('paiements.show', compact('frais'));
    }

    /**
     * Afficher le formulaire de paiement d'une tranche
     */
    public function payerTranche(TranchePaiement $tranche)
    {
        $tranche->load('fraisScolarite.eleve.utilisateur', 'fraisScolarite.eleve.classe');
        return view('paiements.payer-tranche', compact('tranche'));
    }

    /**
     * Enregistrer le paiement d'une tranche
     */
    public function enregistrerPaiementTranche(Request $request, TranchePaiement $tranche)
    {
        $request->validate([
            'montant_paye' => 'required|numeric|min:0|max:' . $tranche->montant_restant,
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference_paiement' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $this->paiementScolariteService->enregistrerPaiementTranche(
                $tranche,
                (float) $request->montant_paye,
                $request->date_paiement,
                $request->mode_paiement,
                $request->reference_paiement,
                $request->observations,
                (int) auth()->id()
            );

            DB::commit();
            return redirect()->route('paiements.show', $tranche->fraisScolarite)
                ->with('success', 'Paiement enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de paiement direct (sans tranches)
     */
    public function payerDirect(FraisScolarite $frais)
    {
        $frais->load('eleve.utilisateur', 'eleve.classe');
        return view('paiements.payer-direct', compact('frais'));
    }

    /**
     * Enregistrer un paiement direct
     */
    public function enregistrerPaiementDirect(Request $request, FraisScolarite $frais)
    {
        $request->validate([
            'montant_paye' => 'required|numeric|min:0|max:' . $frais->montant_restant,
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference_paiement' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Créer le paiement
            $paiement = Paiement::create([
                'frais_scolarite_id' => $frais->id,
                'montant_paye' => $request->montant_paye,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement,
                'reference_paiement' => $request->reference_paiement,
                'observations' => $request->observations,
                'encaisse_par' => auth()->id()
            ]);

            // Répercuter le paiement direct sur les tranches séquentiellement
            $this->repartirPaiementsSurTranches($frais, $request->date_paiement);

            // Vérifier si le frais est entièrement payé (après répartition)
            $frais->refresh();
            if ($frais->montant_restant <= 0.00001) {
                $frais->update(['statut' => 'paye']);
            }

            // Créer automatiquement une entrée comptable
            $this->paiementScolariteService->creerEntreeComptable($paiement, $frais);

            DB::commit();
            return redirect()->route('paiements.show', $frais)
                ->with('success', 'Paiement enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Supprime les tranches au-delà de nombre_tranches (créées par erreur via facturation)
     * et réinitialise les tranches valides pour permettre une nouvelle répartition.
     */
    private function nettoyerTranchesHorsPeriode(FraisScolarite $frais): void
    {
        if (!$frais->paiement_par_tranches || !$frais->nombre_tranches) {
            return;
        }

        $frais->loadMissing(['tranchesPaiement', 'paiements']);

        $orphelines = $frais->tranchesPaiement->filter(
            fn ($t) => $t->numero_tranche > (int) $frais->nombre_tranches
        );

        if ($orphelines->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($frais, $orphelines) {
            $orphelineIds = $orphelines->pluck('id');

            Paiement::whereIn('tranche_paiement_id', $orphelineIds)
                ->update(['tranche_paiement_id' => null]);

            TranchePaiement::whereIn('id', $orphelineIds)->delete();

            foreach ($frais->tranchesPaiement as $tranche) {
                if ($tranche->numero_tranche > (int) $frais->nombre_tranches) {
                    continue;
                }
                $tranche->update([
                    'montant_paye' => 0,
                    'statut' => 'en_attente',
                    'date_paiement' => null,
                ]);
            }

            $frais->update(['statut' => 'en_attente']);
        });

        $frais->unsetRelation('tranchesPaiement');
        $frais->load('tranchesPaiement');
    }

    /**
     * Répartit l'intégralité des paiements enregistrés sur les tranches restantes
     * de manière séquentielle (Mois 1 -> n). Idempotent.
     */
    private function repartirPaiementsSurTranches(FraisScolarite $frais, $datePaiement = null): void
    {
        if (!$frais->paiement_par_tranches) {
            return;
        }

        // Recharger pour disposer des totaux actuels
        $frais->loadMissing(['tranchesPaiement', 'paiements']);

        $totalPaye = (float) $frais->paiements->sum('montant_paye');
        $montantAlloue = 0.0;

        // Réinitialiser l'état calculé (sans perdre l'historique des paiements)
        foreach ($frais->tranchesPaiement as $t) {
            $montantAlloue += (float) $t->montant_paye;
        }

        $resteAAllouer = max(0.0, $totalPaye - $montantAlloue);
        if ($resteAAllouer <= 0.0) {
            return;
        }

        $tranches = $frais->tranchesPaiement
            ->filter(fn ($t) => $t->numero_tranche <= (int) $frais->nombre_tranches)
            ->sortBy('numero_tranche');
        foreach ($tranches as $tranche) {
            if ($resteAAllouer <= 0) {
                break;
            }

            $resteTranche = max(0.0, (float) $tranche->montant_tranche - (float) $tranche->montant_paye);
            if ($resteTranche <= 0) {
                continue;
            }

            $verse = min($resteTranche, $resteAAllouer);
            $tranche->montant_paye = (float) $tranche->montant_paye + $verse;
            if ($tranche->montant_paye + 0.00001 >= (float) $tranche->montant_tranche) {
                $tranche->statut = 'paye';
                if ($datePaiement) {
                    $tranche->date_paiement = $datePaiement;
                }
            }
            $tranche->save();
            $resteAAllouer -= $verse;
        }

        // Mettre à jour le statut global si tout est payé
        $frais->refresh();
        if ($frais->montant_restant <= 0.00001) {
            $frais->update(['statut' => 'paye']);
        }
    }

    /**
     * Afficher les rapports de paiement
     */
    public function rapports(Request $request)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Récupérer les filtres
        $dateDebut = $request->get('date_debut', now()->subMonths(6)->format('Y-m-01'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-t'));
        $classeId = $request->get('classe_id');

        // Construire les requêtes de base avec filtrage par année scolaire active
        $fraisQuery = FraisScolarite::whereHas('eleve', function($q) use ($anneeScolaireActive) {
            $q->where('annee_scolaire_id', $anneeScolaireActive->id);
        });
        
        $paiementsQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            });

        // Appliquer le filtre classe si présent
        if ($classeId) {
            $fraisQuery->whereHas('eleve', function($q) use ($classeId, $anneeScolaireActive) {
                $q->where('classe_id', $classeId)
                  ->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
            $paiementsQuery->whereHas('fraisScolarite.eleve', function($q) use ($classeId, $anneeScolaireActive) {
                $q->where('classe_id', $classeId)
                  ->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
        }

        // Statistiques générales
        $stats = [
            'total_frais' => $fraisQuery->count(),
            'frais_payes' => (clone $fraisQuery)->where('statut', 'paye')->count(),
            'frais_en_attente' => (clone $fraisQuery)->where('statut', 'en_attente')->count(),
            'frais_en_retard' => (clone $fraisQuery)->where('statut', 'en_retard')->count(),
            'montant_total' => (clone $fraisQuery)->sum('montant'),
            'montant_paye' => $paiementsQuery->sum('montant_paye')
        ];

        // Statistiques par classe (filtrées par année scolaire active)
        $paiementsParClasse = DB::table('paiements')
            ->join('frais_scolarite', 'paiements.frais_scolarite_id', '=', 'frais_scolarite.id')
            ->join('eleves', 'frais_scolarite.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
            ->where('eleves.annee_scolaire_id', $anneeScolaireActive->id)
            ->select(
                'classes.nom',
                DB::raw('SUM(frais_scolarite.montant) as montant_total'),
                DB::raw('SUM(paiements.montant_paye) as montant_paye'),
                DB::raw('COUNT(DISTINCT frais_scolarite.id) as nombre_frais')
            )
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('montant_paye', 'desc')
            ->get();

        // Paiements récents (filtrés par année scolaire active)
        $paiementsRecents = Paiement::with(['fraisScolarite.eleve.utilisateur', 'fraisScolarite.eleve.classe', 'encaissePar'])
            ->whereHas('fraisScolarite.eleve', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->orderBy('date_paiement', 'desc')
            ->limit(20)
            ->get();

        return view('paiements.rapports', compact('stats', 'paiementsRecents', 'paiementsParClasse', 'anneeScolaireActive'));
    }

    /**
     * Créer automatiquement les frais d'inscription et de scolarité pour un élève
     */
    public function creerFraisAutomatiques(Eleve $eleve, $gratuitInscription = false, $gratuitReinscription = false)
    {
        // Debug pour voir les valeurs reçues
        \Log::info('creerFraisAutomatiques appelé avec:', [
            'eleve_id' => $eleve->id,
            'gratuitInscription' => $gratuitInscription,
            'gratuitReinscription' => $gratuitReinscription,
            'type_inscription' => $eleve->type_inscription
        ]);
        
        // Vérifier si l'élève est exempté des frais
        if ($eleve->exempte_frais) {
            return;
        }

        // Récupérer le tarif de la classe
        $tarif = TarifClasse::where('classe_id', $eleve->classe_id)
            ->where('actif', true)
            ->first();

        if (!$tarif) {
            return; // Pas de tarif configuré pour cette classe
        }

        DB::beginTransaction();
        try {
            // 1. Créer les frais d'inscription ou de réinscription
            if ($eleve->type_inscription === 'nouvelle' && $tarif->frais_inscription > 0) {
                // Frais d'inscription pour nouvelle inscription
                $montantInscription = $gratuitInscription ? 0 : $tarif->frais_inscription;
                $statutInscription = $gratuitInscription ? 'paye' : 'en_attente';
                
                FraisScolarite::create([
                    'eleve_id' => $eleve->id,
                    'libelle' => 'Frais d\'inscription' . ($gratuitInscription ? ' (GRATUIT)' : ''),
                    'montant' => $montantInscription,
                    'date_echeance' => $gratuitInscription ? now() : now()->addDays(30), // 30 jours pour payer
                    'statut' => $statutInscription,
                    'type_frais' => 'inscription',
                    'description' => $gratuitInscription ? 'Frais d\'inscription GRATUIT pour l\'année scolaire' : 'Frais d\'inscription pour l\'année scolaire',
                    'paiement_par_tranches' => false
                ]);
            } elseif ($eleve->type_inscription === 'reinscription' && $tarif->frais_reinscription > 0) {
                // Frais de réinscription
                $montantReinscription = $gratuitReinscription ? 0 : $tarif->frais_reinscription;
                $statutReinscription = $gratuitReinscription ? 'paye' : 'en_attente';
                
                FraisScolarite::create([
                    'eleve_id' => $eleve->id,
                    'libelle' => 'Frais de réinscription' . ($gratuitReinscription ? ' (GRATUIT)' : ''),
                    'montant' => $montantReinscription,
                    'date_echeance' => $gratuitReinscription ? now() : now()->addDays(30), // 30 jours pour payer
                    'statut' => $statutReinscription,
                    'type_frais' => 'reinscription',
                    'description' => $gratuitReinscription ? 'Frais de réinscription GRATUIT pour l\'année scolaire' : 'Frais de réinscription pour l\'année scolaire',
                    'paiement_par_tranches' => false
                ]);
            }

            // Note: Les frais de scolarité, cantine et transport doivent être créés manuellement
            // par l'utilisateur ou le comptable via l'interface de gestion des paiements

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Générer un reçu PDF pour un paiement
     */
    public function genererRecu(FraisScolarite $frais, Paiement $paiement = null)
    {
        $frais->load(['eleve.utilisateur', 'eleve.classe', 'paiements.encaissePar']);
        
        // Si aucun paiement spécifique n'est fourni, prendre le dernier
        if (!$paiement) {
            $paiement = $frais->paiements()->latest()->first();
        }
        
        if (!$paiement) {
            return redirect()->back()->with('error', 'Aucun paiement trouvé pour ce frais.');
        }

        $paiement->load('encaissePar');
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::principal();
        $schoolInfo = [
            'school_name' => $etablissement ? $etablissement->nom : 'École A2S',
            'school_address' => $etablissement ? $etablissement->adresse : 'Adresse de l\'école',
            'school_phone' => $etablissement ? $etablissement->telephone : 'Téléphone de l\'école',
            'school_email' => $etablissement ? $etablissement->email : 'email@ecole.com'
        ];
        
        // Générer le contenu HTML du reçu
        $html = view('paiements.recu-pdf', compact('frais', 'paiement', 'schoolInfo'))->render();
        
        // Créer une réponse avec le contenu HTML
        $response = response($html);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->header('Content-Disposition', 'inline; filename="recu_paiement_' . $paiement->id . '.html"');
        
        return $response;
    }

    /**
     * Générer un reçu à partir d'une entrée comptable
     */
    public function genererRecuFromEntree(Entree $entree)
    {
        if ($entree->reference) {
            $facture = \App\Models\Facture::where('numero_facture', $entree->reference)->first();
            if ($facture) {
                return redirect()->route('factures.pdf', $facture);
            }
        }

        // Trouver le paiement correspondant à cette entrée
        $paiement = Paiement::where('reference_paiement', $entree->reference)
            ->where('montant_paye', $entree->montant)
            ->where('date_paiement', $entree->date_entree)
            ->where('encaisse_par', $entree->enregistre_par)
            ->first();
        
        if (!$paiement) {
            return redirect()->back()->with('error', 'Aucun paiement correspondant trouvé pour cette entrée.');
        }
        
        // Utiliser la méthode existante pour générer le reçu
        return $this->genererRecu($paiement->fraisScolarite, $paiement);
    }

    /**
     * Supprimer définitivement un frais de scolarité
     */
    public function destroy(FraisScolarite $frais)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer des frais.');
        }

        try {
            DB::beginTransaction();

            // Récupérer les informations avant suppression pour les logs
            $eleveNom = $frais->eleve->utilisateur->nom . ' ' . $frais->eleve->utilisateur->prenom;
            $fraisLibelle = $frais->libelle;

            // Supprimer toutes les entrées comptables associées
            $paiements = $frais->paiements;
            foreach ($paiements as $paiement) {
                $entrees = Entree::where('reference', $paiement->reference_paiement)
                    ->where('montant', $paiement->montant_paye)
                    ->where('date_entree', $paiement->date_paiement)
                    ->where('enregistre_par', $paiement->encaisse_par)
                    ->get();
                
                foreach ($entrees as $entree) {
                    $entree->delete();
                }
            }

            // Supprimer tous les paiements
            $frais->paiements()->delete();

            // Supprimer toutes les tranches de paiement
            $frais->tranchesPaiement()->delete();

            // Supprimer le frais de scolarité
            $frais->delete();

            DB::commit();

            return redirect()->route('paiements.index')
                ->with('success', "Les frais '{$fraisLibelle}' de {$eleveNom} ont été supprimés avec succès.");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erreur lors de la suppression des frais: ' . $e->getMessage());
        }
    }
}
