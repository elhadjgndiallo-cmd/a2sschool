<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Facture;
use App\Services\FacturationService;
use Illuminate\Http\Request;

class FacturationController extends Controller
{
    public function __construct(
        private FacturationService $facturationService
    ) {}

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à consulter les factures.');
        }

        $query = Facture::with(['eleve.utilisateur', 'eleve.classe', 'generePar']);

        if ($request->filled('classe_id')) {
            $query->whereHas('eleve', fn ($q) => $q->where('classe_id', $request->classe_id));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_facture', 'like', "%{$search}%")
                    ->orWhereHas('eleve.utilisateur', function ($uq) use ($search) {
                        $uq->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    })
                    ->orWhereHas('eleve', fn ($eq) => $eq->where('numero_etudiant', 'like', "%{$search}%"));
            });
        }

        $factures = $query->orderByDesc('date_facture')->orderByDesc('id')->paginate(20);
        $classes = Classe::orderBy('nom')->get();

        return view('factures.index', compact('factures', 'classes'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des factures.');
        }

        $anneeScolaire = AnneeScolaire::anneeActive();
        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire active.');
        }

        $eleve = null;
        if ($request->filled('eleve_id')) {
            $eleve = Eleve::with(['utilisateur', 'classe'])->find($request->eleve_id);
        }

        return view('factures.create', compact('eleve', 'anneeScolaire'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des factures.');
        }

        $mode = $request->input('mode', 'mois');

        $rules = [
            'eleve_id' => 'required|exists:eleves,id',
            'mode' => 'required|in:mois,montant',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'remise_type' => 'required|in:pourcentage,montant',
            'remise_valeur' => 'nullable|numeric|min:0',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference_paiement' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:1000',
        ];

        if ($mode === 'montant') {
            $rules['montant_verse'] = 'required|numeric|min:1';
            $rules['type_frais_cible'] = 'required|in:scolarite,cantine,transport';
        } else {
            $rules['lignes'] = 'required|array|min:1';
            $rules['lignes.*'] = 'required|string';
        }

        $request->validate($rules);

        try {
            $facture = $this->facturationService->emettreFacture([
                'eleve_id' => $request->eleve_id,
                'mode' => $mode,
                'montant_verse' => (float) ($request->montant_verse ?? 0),
                'type_frais_cible' => $request->type_frais_cible,
                'date_facture' => $request->date_facture,
                'date_echeance' => $request->date_echeance,
                'remise_type' => $request->remise_type,
                'remise_valeur' => (float) ($request->remise_valeur ?? 0),
                'mode_paiement' => $request->mode_paiement,
                'reference_paiement' => $request->reference_paiement,
                'observations' => $request->observations,
                'lignes' => $request->lignes ?? [],
            ]);

            return redirect()->route('factures.show', $facture)
                ->with('success', 'Facture émise et paiement enregistré avec succès.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Facture $facture)
    {
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à consulter cette facture.');
        }

        $facture->load(['lignes.fraisScolarite', 'lignes.paiement', 'eleve.utilisateur', 'eleve.classe', 'generePar', 'anneeScolaire']);

        return view('factures.show', compact('facture'));
    }

    public function pdf(Facture $facture)
    {
        if (!auth()->user()->hasPermission('paiements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à consulter cette facture.');
        }

        $facture->load(['lignes', 'eleve.utilisateur', 'eleve.classe', 'generePar', 'anneeScolaire']);

        $etablissement = \App\Models\Etablissement::principal();
        $schoolInfo = [
            'school_name' => $etablissement?->nom ?? 'École A2S',
            'school_address' => $etablissement?->adresse ?? '',
            'school_phone' => $etablissement?->telephone ?? '',
            'school_email' => $etablissement?->email ?? '',
        ];

        $html = view('factures.pdf', compact('facture', 'schoolInfo'))->render();

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="facture_' . $facture->numero_facture . '.html"');
    }

    public function searchEleves(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $query = Eleve::with(['utilisateur', 'classe']);

        $anneeActive = AnneeScolaire::anneeActive();
        if ($anneeActive) {
            $query->where('annee_scolaire_id', $anneeActive->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('utilisateur', function ($uq) use ($search) {
                    $uq->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                })->orWhere('numero_etudiant', 'like', "%{$search}%");
            });
        }

        return response()->json($query->limit(10)->get());
    }

    public function lignesEleve(Eleve $eleve)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($eleve->exempte_frais) {
            return response()->json(['error' => 'Cet élève est exempté de frais.', 'lignes' => []], 422);
        }

        return response()->json([
            'lignes' => $this->facturationService->getLignesDisponibles($eleve),
        ]);
    }

    public function previewTotaux(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'lignes' => 'required|array|min:1',
            'remise_type' => 'required|in:pourcentage,montant',
            'remise_valeur' => 'nullable|numeric|min:0',
        ]);

        $eleve = Eleve::findOrFail($request->eleve_id);
        $disponibles = collect($this->facturationService->getLignesDisponibles($eleve))->keyBy('id');

        $selection = [];
        foreach ($request->lignes as $id) {
            $ligne = $disponibles->get($id);
            if ($ligne) {
                $selection[] = ['montant' => (float) $ligne['montant']];
            }
        }

        $totaux = $this->facturationService->calculerTotaux(
            $selection,
            $request->remise_type,
            (float) ($request->remise_valeur ?? 0)
        );

        return response()->json($totaux);
    }

    public function previewRepartition(Request $request)
    {
        if (!auth()->user()->hasPermission('paiements.create')) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'montant_verse' => 'required|numeric|min:1',
            'type_frais_cible' => 'required|in:scolarite,cantine,transport',
            'remise_type' => 'required|in:pourcentage,montant',
            'remise_valeur' => 'nullable|numeric|min:0',
        ]);

        try {
            $eleve = Eleve::findOrFail($request->eleve_id);

            return response()->json(
                $this->facturationService->previewRepartitionMontant(
                    $eleve,
                    $request->type_frais_cible,
                    (float) $request->montant_verse,
                    $request->remise_type,
                    (float) ($request->remise_valeur ?? 0)
                )
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
