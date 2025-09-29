<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepenseController extends Controller
{
    /**
     * Afficher la liste des dépenses
     */
    public function index(Request $request)
    {
        $query = Depense::with(['approuvePar', 'payePar']);

        // Filtres
        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_depense', [$request->date_debut, $request->date_fin]);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('libelle', 'like', '%' . $request->search . '%')
                  ->orWhere('beneficiaire', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_facture', 'like', '%' . $request->search . '%');
            });
        }

        $depenses = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('depenses.index', compact('depenses'));
    }

    /**
     * Afficher le formulaire de création de dépense
     */
    public function create()
    {
        $enseignants = Enseignant::join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.name')
            ->get();
        return view('depenses.create', compact('enseignants'));
    }

    /**
     * Enregistrer une nouvelle dépense
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'type_depense' => 'required|in:salaire_enseignant,salaire_personnel,achat_materiel,maintenance,electricite,eau,nourriture,transport,communication,formation,autre',
            'description' => 'nullable|string',
            'beneficiaire' => 'nullable|string|max:255',
            'reference_facture' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        $depense = Depense::create($request->all());

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Dépense créée avec succès.');
    }

    /**
     * Afficher les détails d'une dépense
     */
    public function show(Depense $depense)
    {
        $depense->load(['approuvePar', 'payePar']);
        return view('depenses.show', compact('depense'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Depense $depense)
    {
        $enseignants = Enseignant::with('utilisateur')->get()->sortBy('utilisateur.nom');
        return view('depenses.edit', compact('depense', 'enseignants'));
    }

    /**
     * Mettre à jour une dépense
     */
    public function update(Request $request, Depense $depense)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'type_depense' => 'required|in:salaire_enseignant,salaire_personnel,achat_materiel,maintenance,electricite,eau,nourriture,transport,communication,formation,autre',
            'description' => 'nullable|string',
            'beneficiaire' => 'nullable|string|max:255',
            'reference_facture' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        $depense->update($request->all());

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Dépense mise à jour avec succès.');
    }

    /**
     * Approuver une dépense
     */
    public function approuver(Depense $depense)
    {
        $depense->approuver(auth()->id());

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Dépense approuvée avec succès.');
    }

    /**
     * Afficher le formulaire de paiement
     */
    public function payer(Depense $depense)
    {
        return view('depenses.payer', compact('depense'));
    }

    /**
     * Enregistrer le paiement d'une dépense
     */
    public function enregistrerPaiement(Request $request, Depense $depense)
    {
        $request->validate([
            'mode_paiement' => 'required|in:especes,cheque,virement,carte',
            'reference_paiement' => 'nullable|string|max:255',
            'date_paiement' => 'required|date',
            'observations' => 'nullable|string'
        ]);

        $depense->marquerCommePaye(
            auth()->id(),
            $request->mode_paiement,
            $request->reference_paiement,
            $request->date_paiement
        );

        if ($request->observations) {
            $depense->update(['observations' => $request->observations]);
        }

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Annuler une dépense
     */
    public function annuler(Depense $depense)
    {
        $depense->annuler();

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Dépense annulée avec succès.');
    }

    /**
     * Supprimer une dépense
     */
    public function destroy(Depense $depense)
    {
        $depense->delete();

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense supprimée avec succès.');
    }

    /**
     * Afficher les rapports de dépenses
     */
    public function rapports(Request $request)
    {
        $dateDebut = $request->get('date_debut', now()->startOfMonth());
        $dateFin = $request->get('date_fin', now()->endOfMonth());

        $stats = [
            'total_depenses' => Depense::parPeriode($dateDebut, $dateFin)->count(),
            'depenses_payees' => Depense::parPeriode($dateDebut, $dateFin)->payees()->count(),
            'depenses_en_attente' => Depense::parPeriode($dateDebut, $dateFin)->enAttente()->count(),
            'depenses_approuvees' => Depense::parPeriode($dateDebut, $dateFin)->approuvees()->count(),
            'montant_total' => Depense::parPeriode($dateDebut, $dateFin)->sum('montant'),
            'montant_paye' => Depense::parPeriode($dateDebut, $dateFin)->payees()->sum('montant')
        ];

        // Dépenses par type
        $depensesParType = Depense::parPeriode($dateDebut, $dateFin)
            ->selectRaw('type_depense, COUNT(*) as count, SUM(montant) as total')
            ->groupBy('type_depense')
            ->get();

        // Dépenses récentes
        $depensesRecentes = Depense::with(['approuvePar', 'payePar'])
            ->parPeriode($dateDebut, $dateFin)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('depenses.rapports', compact('stats', 'depensesParType', 'depensesRecentes', 'dateDebut', 'dateFin'));
    }

    /**
     * Créer une dépense de salaire pour un enseignant
     */
    public function creerSalaireEnseignant(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $enseignant = Enseignant::findOrFail($request->enseignant_id);

        $depense = Depense::create([
            'libelle' => 'Salaire ' . $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom,
            'montant' => $request->montant,
            'date_depense' => $request->date_depense,
            'type_depense' => 'salaire_enseignant',
            'description' => $request->description ?? 'Salaire mensuel de l\'enseignant',
            'beneficiaire' => $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom,
            'statut' => 'en_attente'
        ]);

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Dépense de salaire créée avec succès.');
    }
}
