<?php

namespace App\Http\Controllers;

use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\Eleve;
use App\Models\Entree;
use App\Models\TarifClasse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
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
        
        $query = FraisScolarite::with(['eleve.utilisateur', 'eleve.classe', 'tranchesPaiement', 'paiements']);
        
        // Filtre par classe
        if ($request->filled('classe_id')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }
        
        // Filtre par matricule
        if ($request->filled('matricule')) {
            $query->whereHas('eleve', function($q) use ($request) {
                $q->where('numero_etudiant', 'like', '%' . $request->matricule . '%');
            });
        }
        
        // Filtre par nom de l'élève
        if ($request->filled('nom')) {
            $query->whereHas('eleve.utilisateur', function($q) use ($request) {
                $q->where(function($subQuery) use ($request) {
                    $subQuery->where('nom', 'like', '%' . $request->nom . '%')
                            ->orWhere('prenom', 'like', '%' . $request->nom . '%');
                });
            });
        }
        
        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $fraisScolarite = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('paiements.index', compact('fraisScolarite'));
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
        // Assurer la cohérence: répartir tout paiement non encore affecté aux tranches
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
            // Créer le paiement
            $paiement = Paiement::create([
                'frais_scolarite_id' => $tranche->frais_scolarite_id,
                'tranche_paiement_id' => $tranche->id,
                'montant_paye' => $request->montant_paye,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement,
                'reference_paiement' => $request->reference_paiement,
                'observations' => $request->observations,
                'encaisse_par' => auth()->id()
            ]);

            // Mettre à jour la tranche
            $nouveauMontantPaye = $tranche->montant_paye + $request->montant_paye;
            $tranche->update([
                'montant_paye' => $nouveauMontantPaye,
                'date_paiement' => $request->date_paiement,
                'statut' => $nouveauMontantPaye >= $tranche->montant_tranche ? 'paye' : 'en_attente'
            ]);

            // Vérifier si toutes les tranches sont payées
            $frais = $tranche->fraisScolarite;
            if ($frais->toutesTranchesPayees()) {
                $frais->update(['statut' => 'paye']);
            }

            // Créer automatiquement une entrée comptable
            $this->creerEntreeComptable($paiement, $frais);

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
            $this->creerEntreeComptable($paiement, $frais);

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

        $tranches = $frais->tranchesPaiement->sortBy('numero_tranche');
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
    public function rapports()
    {
        $stats = [
            'total_frais' => FraisScolarite::count(),
            'frais_payes' => FraisScolarite::where('statut', 'paye')->count(),
            'frais_en_attente' => FraisScolarite::where('statut', 'en_attente')->count(),
            'frais_en_retard' => FraisScolarite::where('statut', 'en_retard')->count(),
            'montant_total' => FraisScolarite::sum('montant'),
            'montant_paye' => Paiement::sum('montant_paye')
        ];

        $paiementsRecents = Paiement::with(['fraisScolarite.eleve', 'encaissePar'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('paiements.rapports', compact('stats', 'paiementsRecents'));
    }

    /**
     * Créer automatiquement les frais d'inscription et de scolarité pour un élève
     */
    public function creerFraisAutomatiques(Eleve $eleve)
    {
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
                FraisScolarite::create([
                    'eleve_id' => $eleve->id,
                    'libelle' => 'Frais d\'inscription',
                    'montant' => $tarif->frais_inscription,
                    'date_echeance' => now()->addDays(30), // 30 jours pour payer
                    'statut' => 'en_attente',
                    'type_frais' => 'inscription',
                    'description' => 'Frais d\'inscription pour l\'année scolaire',
                    'paiement_par_tranches' => false
                ]);
            } elseif ($eleve->type_inscription === 'reinscription' && $tarif->frais_reinscription > 0) {
                // Frais de réinscription
                FraisScolarite::create([
                    'eleve_id' => $eleve->id,
                    'libelle' => 'Frais de réinscription',
                    'montant' => $tarif->frais_reinscription,
                    'date_echeance' => now()->addDays(30), // 30 jours pour payer
                    'statut' => 'en_attente',
                    'type_frais' => 'reinscription',
                    'description' => 'Frais de réinscription pour l\'année scolaire',
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
        
        // Générer le contenu HTML du reçu
        $html = view('paiements.recu-pdf', compact('frais', 'paiement'))->render();
        
        // Créer une réponse avec le contenu HTML
        $response = response($html);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->header('Content-Disposition', 'inline; filename="recu_paiement_' . $paiement->id . '.html"');
        
        return $response;
    }

    /**
     * Créer automatiquement une entrée comptable pour un paiement
     */
    private function creerEntreeComptable(Paiement $paiement, FraisScolarite $frais)
    {
        $eleve = $frais->eleve;
        $classe = $eleve->classe;
        
        // Créer l'entrée comptable
        Entree::create([
            'libelle' => "Paiement frais de scolarité - {$eleve->utilisateur->nom} ({$classe->nom})",
            'description' => "Paiement de {$paiement->montant_paye} GNF pour les frais de scolarité de l'élève {$eleve->utilisateur->nom} de la classe {$classe->nom}. Référence paiement: {$paiement->reference_paiement}",
            'montant' => $paiement->montant_paye,
            'date_entree' => $paiement->date_paiement,
            'source' => 'Paiements scolaires',
            'mode_paiement' => $paiement->mode_paiement,
            'reference' => $paiement->reference_paiement,
            'enregistre_par' => $paiement->encaisse_par
        ]);
    }
}
