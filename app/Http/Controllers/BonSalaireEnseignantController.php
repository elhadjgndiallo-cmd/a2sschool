<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\BonSalaireEnseignant;
use App\Models\Depense;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BonSalaireEnseignantController extends Controller
{
    public function index(Request $request)
    {
        $anneeScolaire = AnneeScolaire::anneeActive();

        $query = BonSalaireEnseignant::with(['enseignant.utilisateur', 'anneeScolaire', 'creePar', 'salaireEnseignant']);

        if ($anneeScolaire && !$request->filled('annee_scolaire_id')) {
            $query->where(function ($q) use ($anneeScolaire) {
                $q->where('annee_scolaire_id', $anneeScolaire->id)->orWhereNull('annee_scolaire_id');
            });
        } elseif ($request->filled('annee_scolaire_id')) {
            $query->where('annee_scolaire_id', $request->annee_scolaire_id);
        }

        if ($request->filled('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $bons = $query->orderByDesc('date_bon')->orderByDesc('id')->paginate(20);
        $enseignants = Enseignant::listeDeroulante($anneeScolaire?->id);
        $anneesScolaires = AnneeScolaire::orderByDesc('date_debut')->get();

        return view('salaires.bons.index', compact('bons', 'enseignants', 'anneeScolaire', 'anneesScolaires'));
    }

    public function create()
    {
        $anneeScolaire = AnneeScolaire::anneeActive();
        $enseignants = Enseignant::listeDeroulante($anneeScolaire?->id);

        return view('salaires.bons.create', compact('enseignants', 'anneeScolaire'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'montant' => 'required|numeric|min:1',
            'date_bon' => 'required|date',
            'mois_reference' => 'nullable|date',
            'mode_paiement' => 'required|in:especes,cheque,virement,carte',
            'reference_paiement' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:1000',
        ]);

        $enseignant = Enseignant::with('utilisateur')->findOrFail($request->enseignant_id);
        $anneeScolaire = AnneeScolaire::anneeActive();

        DB::beginTransaction();
        try {
            $bon = BonSalaireEnseignant::create([
                'enseignant_id' => $enseignant->id,
                'annee_scolaire_id' => $anneeScolaire?->id ?? $enseignant->annee_scolaire_id,
                'numero_bon' => BonSalaireEnseignant::genererNumeroBon(),
                'montant' => $request->montant,
                'date_bon' => $request->date_bon,
                'mois_reference' => $request->mois_reference ? $request->mois_reference . '-01' : null,
                'statut' => 'actif',
                'mode_paiement' => $request->mode_paiement,
                'reference_paiement' => $request->reference_paiement,
                'observations' => $request->observations,
                'cree_par' => auth()->id(),
            ]);

            $depensePayload = [
                'libelle' => 'Avance salaire — ' . $bon->numero_bon,
                'montant' => $bon->montant,
                'date_depense' => $bon->date_bon,
                'type_depense' => 'salaire_enseignant',
                'description' => 'Bon de salaire (avance) pour '
                    . $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom,
                'beneficiaire' => $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom,
                'statut' => 'paye',
                'mode_paiement' => $bon->mode_paiement,
                'reference_paiement' => $bon->reference_paiement ?? $bon->numero_bon,
                'paye_par' => auth()->id(),
                'date_paiement' => $bon->date_bon,
                'observations' => 'Avance sur salaire — sera déduite au prochain bulletin | ' . $bon->numero_bon,
                'annee_scolaire_id' => $bon->annee_scolaire_id,
            ];

            if (Depense::hasBonSalaireLinkColumn()) {
                $depensePayload['bon_salaire_enseignant_id'] = $bon->id;
            }

            Depense::create($depensePayload);

            $this->clearComptabiliteCache($bon->annee_scolaire_id);

            DB::commit();

            return redirect()->route('salaires.bons.index')
                ->with('success', 'Bon de salaire (avance) créé avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(BonSalaireEnseignant $bon)
    {
        $bon->load(['enseignant.utilisateur', 'anneeScolaire', 'creePar', 'salaireEnseignant']);
        $etablissement = \App\Models\Etablissement::first();

        return view('salaires.bons.show', compact('bon', 'etablissement'));
    }

    public function destroy(BonSalaireEnseignant $bon)
    {
        if ($bon->statut === 'deduit') {
            return back()->with('error', 'Ce bon a déjà été déduit sur un bulletin et ne peut pas être supprimé.');
        }

        if (Depense::hasBonSalaireLinkColumn()) {
            Depense::where('bon_salaire_enseignant_id', $bon->id)->delete();
        } else {
            Depense::where('observations', 'like', '%' . $bon->numero_bon . '%')
                ->where('type_depense', 'salaire_enseignant')
                ->delete();
        }

        $bon->update(['statut' => 'annule']);

        $this->clearComptabiliteCache($bon->annee_scolaire_id);

        return redirect()->route('salaires.bons.index')
            ->with('success', 'Bon de salaire annulé.');
    }

    private function clearComptabiliteCache(?int $anneeScolaireId): void
    {
        if (!$anneeScolaireId) {
            return;
        }

        Cache::forget('comptabilite_stats_' . $anneeScolaireId);
        Cache::forget('comptabilite_sorties_stats_' . $anneeScolaireId);
    }
}
