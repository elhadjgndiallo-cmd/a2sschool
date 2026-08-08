<?php

namespace App\Http\Controllers;

use App\Models\Entree;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use App\Services\ComptabiliteEntreesStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EntreeController extends Controller
{
    /**
     * Afficher la liste des entrées
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('entrees.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();
        $entreesStats = app(ComptabiliteEntreesStatsService::class);

        if (!$anneeScolaire) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        $listRequest = $request->duplicate();

        $allEntries = $entreesStats->buildListEntries($listRequest, $anneeScolaire);

        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedEntries = new \Illuminate\Pagination\LengthAwarePaginator(
            $allEntries->slice($offset, $perPage),
            $allEntries->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        $paginatedEntries->appends(request()->query());

        $statsEntrees = $entreesStats->statsFromEntries($allEntries);
        $totalEntreesManuelles = $statsEntrees['total_manuelles'];
        $totalPaiementsFrais = $statsEntrees['total_paiements'];
        $totalGeneral = $statsEntrees['total'];

        $sources = Entree::query()
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        return view('entrees.index', compact(
            'paginatedEntries',
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
            return redirect()->route('entrees.index')
                ->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $anneeScolaire = \App\Models\AnneeScolaire::anneeActive();

        return view('entrees.create', compact('anneeScolaire'));
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
            'montant' => 'required|numeric|min:0|max:9999999999999.99',
            'date_entree' => 'required|date',
            'source' => 'required|string|max:255',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte,mobile_money',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            $anneeActive = \App\Models\AnneeScolaire::anneeActive();

            Entree::create([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'montant' => $request->montant,
                'date_entree' => $request->date_entree,
                'source' => $request->source,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
                'enregistre_par' => auth()->id(),
                'annee_scolaire_id' => $anneeActive?->id,
            ]);

            $this->clearComptabiliteEntreesCache();

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
            $entree->update($request->only([
                'libelle', 'description', 'montant', 'date_entree',
                'source', 'mode_paiement', 'reference',
            ]));

            $this->clearComptabiliteEntreesCache();

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

            $this->clearComptabiliteEntreesCache();

            return redirect()->route('entrees.index')
                ->with('success', 'Entrée supprimée avec succès.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    private function clearComptabiliteEntreesCache(): void
    {
        $annee = \App\Models\AnneeScolaire::anneeActive();
        if ($annee) {
            Cache::forget('comptabilite_entrees_stats_' . $annee->id);
            Cache::forget('comptabilite_stats_' . $annee->id);
        }
    }
}