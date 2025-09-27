<?php

namespace App\Http\Controllers;

use App\Models\Entree;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntreeController extends Controller
{
    /**
     * Afficher la liste des entrées
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $query = Entree::with('enregistrePar')
            ->orderBy('date_entree', 'desc');

        // Filtres
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_debut')) {
            $query->where('date_entree', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_entree', '<=', $request->date_fin);
        }

        $entrees = $query->paginate(20);

        // Récupérer les paiements de frais de scolarité pour les afficher aussi
        $paiementsFrais = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
            ->whereHas('fraisScolarite')
            ->orderBy('date_paiement', 'desc')
            ->paginate(20);

        // Statistiques
        // Entrées manuelles (exclure les entrées créées automatiquement par les paiements scolaires)
        $totalEntreesManuelles = Entree::where('source', '!=', 'Paiements scolaires')->sum('montant');
        
        // Paiements de frais de scolarité
        $totalPaiementsFrais = Paiement::whereHas('fraisScolarite')->sum('montant_paye');
        
        // Total général = entrées manuelles + paiements scolaires
        $totalGeneral = $totalEntreesManuelles + $totalPaiementsFrais;

        // Sources disponibles
        $sources = Entree::select('source')->distinct()->pluck('source');

        return view('entrees.index', compact(
            'entrees', 
            'paiementsFrais', 
            'totalEntreesManuelles', 
            'totalPaiementsFrais', 
            'totalGeneral',
            'sources'
        ));
    }

    /**
     * Afficher le formulaire de création d'entrée
     */
    public function create()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        return view('entrees.create');
    }

    /**
     * Enregistrer une nouvelle entrée
     */
    public function store(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_entree' => 'required|date',
            'source' => 'required|string|max:255',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            Entree::create([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'montant' => $request->montant,
                'date_entree' => $request->date_entree,
                'source' => $request->source,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
                'enregistre_par' => auth()->id()
            ]);

            return redirect()->route('entrees.index')
                ->with('success', 'Entrée enregistrée avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une entrée
     */
    public function show(Entree $entree)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $entree->load('enregistrePar');
        return view('entrees.show', compact('entree'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Entree $entree)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        return view('entrees.edit', compact('entree'));
    }

    /**
     * Mettre à jour une entrée
     */
    public function update(Request $request, Entree $entree)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_entree' => 'required|date',
            'source' => 'required|string|max:255',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            $entree->update($request->all());

            return redirect()->route('entrees.index')
                ->with('success', 'Entrée mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une entrée
     */
    public function destroy(Entree $entree)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('entrees.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        try {
            $entree->delete();

            return redirect()->route('entrees.index')
                ->with('success', 'Entrée supprimée avec succès.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}