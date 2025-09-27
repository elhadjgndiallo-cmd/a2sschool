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
     * Afficher la liste des frais de scolarité
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        $query = FraisScolarite::with(['eleve.utilisateur', 'eleve.classe', 'tranchesPaiement']);
        
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
        // Récupérer seulement les élèves non exemptés des frais de scolarité
        $eleves = Eleve::with(['utilisateur', 'classe'])
            ->where('exempte_frais', false)
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
            'paiement_par_tranches' => 'boolean',
            'nombre_tranches' => 'required_if:paiement_par_tranches,true|integer|min:2|max:12',
            'periode_tranche' => 'required_if:paiement_par_tranches,true|in:mensuel,trimestriel,semestriel,annuel',
            'date_debut_tranches' => 'required_if:paiement_par_tranches,true|date'
        ]);

        // Vérifier que l'élève n'est pas exempté des frais de scolarité
        $eleve = Eleve::findOrFail($request->eleve_id);
        if ($eleve->exempte_frais) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de créer des frais de scolarité pour un élève exempté.');
        }

        // Vérifier qu'il n'existe pas déjà des frais de scolarité pour cet élève
        $fraisExistants = FraisScolarite::where('eleve_id', $request->eleve_id)
            ->where('type_frais', 'scolarite')
            ->count();
        
        if ($fraisExistants > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Des frais de scolarité existent déjà pour cet élève.');
        }

        DB::beginTransaction();
        try {
            $frais = FraisScolarite::create($request->all());

            // Si paiement par tranches, créer les tranches
            if ($request->paiement_par_tranches) {
                $frais->creerTranchesPaiement();
            }

            DB::commit();
            return redirect()->route('paiements.show', $frais)
                ->with('success', 'Frais de scolarité créé avec succès.');
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

            // Vérifier si le frais est entièrement payé
            if ($frais->montant_restant <= 0) {
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
