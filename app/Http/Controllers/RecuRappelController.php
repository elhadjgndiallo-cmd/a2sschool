<?php

namespace App\Http\Controllers;

use App\Models\RecuRappel;
use App\Models\FraisScolarite;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RecuRappelController extends Controller
{
    /**
     * Afficher la liste des reçus de rappel
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $query = RecuRappel::with(['eleve.utilisateur', 'eleve.classe', 'fraisScolarite', 'generePar']);

        // Filtre par classe
        if ($request->filled('classe_id')) {
            $query->whereHas('eleve', function ($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par date
        if ($request->filled('date_debut')) {
            $query->where('date_rappel', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_rappel', '<=', $request->date_fin);
        }

        // Recherche par nom d'élève
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('eleve.utilisateur', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('numero_etudiant', 'like', "%{$search}%");
            });
        }

        $recusRappel = $query->orderBy('created_at', 'desc')->paginate(20);

        // Récupérer les classes pour le filtre
        $classes = \App\Models\Classe::orderBy('nom')->get();

        return view('recus-rappel.index', compact('recusRappel', 'classes'));
    }

    /**
     * Afficher le formulaire de création d'un reçu de rappel
     */
    public function create(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des reçus de rappel.');
        }

        $eleveId = $request->get('eleve_id');
        $fraisId = $request->get('frais_id');

        $eleve = null;
        $frais = null;

        if ($eleveId) {
            $eleve = Eleve::with(['utilisateur', 'classe', 'fraisScolarite' => function ($query) {
                $query->where('statut', '!=', 'paye');
            }])->find($eleveId);
        }

        if ($fraisId) {
            $frais = FraisScolarite::with(['eleve.utilisateur', 'eleve.classe', 'paiements'])->find($fraisId);
        }

        return view('recus-rappel.create', compact('eleve', 'frais'));
    }

    /**
     * Enregistrer un nouveau reçu de rappel
     */
    public function store(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des reçus de rappel.');
        }

        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'frais_scolarite_id' => 'required|exists:frais_scolarite,id',
            'montant_a_payer' => 'nullable|numeric|min:0',
            'date_echeance' => 'required|date|after:today',
            'observations' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $frais = FraisScolarite::with(['eleve', 'paiements'])->find($request->frais_scolarite_id);
            
            if (!$frais) {
                throw new \Exception('Frais de scolarité non trouvé.');
            }

            // Calculer les montants
            $montantTotalDu = $frais->montant;
            $montantPaye = $frais->paiements->sum('montant_paye');
            $montantRestant = $montantTotalDu - $montantPaye;

            // Créer le reçu de rappel
            $recuRappel = RecuRappel::create([
                'eleve_id' => $request->eleve_id,
                'frais_scolarite_id' => $request->frais_scolarite_id,
                'montant_total_du' => $montantTotalDu,
                'montant_paye' => $montantPaye,
                'montant_restant' => $montantRestant,
                'montant_a_payer' => $request->montant_a_payer,
                'date_rappel' => now()->toDateString(),
                'date_echeance' => $request->date_echeance,
                'observations' => $request->observations,
                'genere_par' => auth()->id()
            ]);

            DB::commit();

            return redirect()->route('recus-rappel.show', $recuRappel)
                ->with('success', 'Reçu de rappel créé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Erreur lors de la création du reçu de rappel: ' . $e->getMessage());
        }
    }

    /**
     * Afficher un reçu de rappel
     */
    public function show(RecuRappel $recuRappel)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir ce reçu de rappel.');
        }

        $recuRappel->load(['eleve.utilisateur', 'eleve.classe', 'fraisScolarite', 'generePar']);

        return view('recus-rappel.show', compact('recuRappel'));
    }

    /**
     * Afficher le formulaire d'édition d'un reçu de rappel
     */
    public function edit(RecuRappel $recuRappel)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier ce reçu de rappel.');
        }

        $recuRappel->load(['eleve.utilisateur', 'eleve.classe', 'fraisScolarite']);

        return view('recus-rappel.edit', compact('recuRappel'));
    }

    /**
     * Mettre à jour un reçu de rappel
     */
    public function update(Request $request, RecuRappel $recuRappel)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier ce reçu de rappel.');
        }

        $request->validate([
            'montant_a_payer' => 'nullable|numeric|min:0',
            'date_echeance' => 'required|date',
            'observations' => 'nullable|string|max:1000'
        ]);

        try {
            $recuRappel->update([
                'montant_a_payer' => $request->montant_a_payer,
                'date_echeance' => $request->date_echeance,
                'observations' => $request->observations
            ]);

            return redirect()->route('recus-rappel.show', $recuRappel)
                ->with('success', 'Reçu de rappel mis à jour avec succès.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un reçu de rappel
     */
    public function destroy(RecuRappel $recuRappel)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer ce reçu de rappel.');
        }

        try {
            $recuRappel->delete();

            return redirect()->route('recus-rappel.index')
                ->with('success', 'Reçu de rappel supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Générer le PDF du reçu de rappel
     */
    public function pdf(RecuRappel $recuRappel)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir ce reçu de rappel.');
        }

        $recuRappel->load(['eleve.utilisateur', 'eleve.classe', 'fraisScolarite', 'generePar']);

            // Récupérer les informations de l'établissement
            $etablissement = \App\Models\Etablissement::principal();
            $schoolInfo = [
                'school_name' => $etablissement ? $etablissement->nom : 'École A2S',
                'school_address' => $etablissement ? $etablissement->adresse : 'Adresse de l\'école',
                'school_phone' => $etablissement ? $etablissement->telephone : 'Téléphone de l\'école',
                'school_email' => $etablissement ? $etablissement->email : 'email@ecole.com'
            ];

        // Générer le contenu HTML du reçu
        $html = view('recus-rappel.pdf', compact('recuRappel', 'schoolInfo'))->render();
        
        // Créer une réponse avec le contenu HTML
        $response = response($html);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->header('Content-Disposition', 'inline; filename="recu_rappel_' . $recuRappel->id . '.html"');
        
        return $response;
    }

    /**
     * Rechercher des élèves pour créer un reçu de rappel
     */
    public function searchEleves(Request $request)
    {
        try {
            // Vérifier les permissions
            if (!auth()->user()->hasPermission('paiements.create')) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $query = Eleve::with(['utilisateur', 'classe', 'fraisScolarite' => function ($query) {
                $query->where('statut', '!=', 'paye');
            }]);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('utilisateur', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('numero_etudiant', 'like', "%{$search}%");
                });
            }

            $eleves = $query->limit(10)->get();

            return response()->json($eleves);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la recherche d\'élèves: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la recherche'], 500);
        }
    }

    /**
     * Récupérer les frais de scolarité d'un élève
     */
    public function getFraisEleve(Request $request, Eleve $eleve)
    {
        try {
            // Vérifier les permissions
            if (!auth()->user()->hasPermission('paiements.create')) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $frais = $eleve->fraisScolarite()
                ->where('statut', '!=', 'paye')
                ->with(['paiements'])
                ->get();

            return response()->json($frais);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des frais: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des frais'], 500);
        }
    }
}