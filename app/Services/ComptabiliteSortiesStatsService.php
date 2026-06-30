<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Depense;
use App\Models\SalaireEnseignant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ComptabiliteSortiesStatsService
{
    /**
     * Total des sorties : dépenses (hors doublons salaires) + salaires enseignants payés.
     */
    public function calculateStats(?Request $request = null, ?AnneeScolaire $anneeScolaire = null): array
    {
        $request = $request ?? new Request();

        if (!$anneeScolaire) {
            return [
                'total' => 0,
                'nombre' => 0,
                'moyenne' => 0,
                'total_depenses' => 0,
                'total_salaires' => 0,
            ];
        }

        $entries = $this->buildListEntries($request, $anneeScolaire);
        $depenses = $entries->where('type', 'depense');
        $salaires = $entries->where('type', 'salaire');

        $totalDepenses = (float) $depenses->sum('montant');
        $totalSalaires = (float) $salaires->sum('montant');
        $total = $totalDepenses + $totalSalaires;
        $nombre = $entries->count();

        return [
            'total' => $total,
            'nombre' => $nombre,
            'moyenne' => $nombre > 0 ? $total / $nombre : 0,
            'total_depenses' => $totalDepenses,
            'total_salaires' => $totalSalaires,
        ];
    }

    /**
     * Plage de dates effective pour filtrer les sorties d'une année scolaire.
     * Pour l'année active, la fin est étendue à aujourd'hui si date_fin est dépassée
     * (sauf si annee_scolaire_complete=true dans la requête).
     */
    public function effectiveSchoolYearDateRange(AnneeScolaire $anneeScolaire, ?Request $request = null): array
    {
        return $this->resolveDateRange($anneeScolaire, $request ?? new Request());
    }

    /**
     * Plage stricte : date_debut → date_fin officielles de l'année scolaire.
     */
    public function strictSchoolYearDateRange(AnneeScolaire $anneeScolaire): array
    {
        return [
            'debut' => $anneeScolaire->date_debut->format('Y-m-d'),
            'fin' => $anneeScolaire->date_fin->format('Y-m-d'),
        ];
    }

    /**
     * Liste unifiée pour comptabilite/sorties, dashboard et statistiques.
     */
    public function buildListEntries(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $depenses = $this->fetchDepenses($request, $anneeScolaire);
        $salairesPayes = $this->fetchSalairesPayes($request, $anneeScolaire);

        $allSorties = collect();

        foreach ($depenses as $depense) {
            if ($this->depenseCorrespondSalairePayeCollection($depense, $salairesPayes)) {
                continue;
            }

            $allSorties->push($this->mapDepenseToListEntry($depense));
        }

        foreach ($salairesPayes as $salaire) {
            $allSorties->push($this->mapSalaireToListEntry($salaire));
        }

        return $this->sortByDateAsc($allSorties);
    }

    /**
     * Une dépense est-elle déjà représentée par un salaire enseignant payé ?
     */
    public function depenseCorrespondSalairePaye(Depense $depense, SalaireEnseignant $salaire): bool
    {
        if ($depense->type_depense !== 'salaire_enseignant') {
            return false;
        }

        if (!$depense->date_depense || !$salaire->date_paiement) {
            return false;
        }

        return $depense->date_depense->format('Y-m-d') === $salaire->date_paiement->format('Y-m-d')
            && abs((float) $depense->montant - (float) $salaire->salaire_net) < 0.01;
    }

    private function depenseCorrespondSalairePayeCollection(Depense $depense, Collection $salairesPayes): bool
    {
        foreach ($salairesPayes as $salaire) {
            if ($this->depenseCorrespondSalairePaye($depense, $salaire)) {
                return true;
            }
        }

        return false;
    }

    private function fetchDepenses(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        $query = Depense::with(['approuvePar', 'payePar'])
            ->where('statut', '!=', 'annule')
            ->whereBetween('date_depense', [$periode['debut'], $periode['fin']]);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }

        return $query->orderBy('date_depense', 'desc')->get();
    }

    private function fetchSalairesPayes(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        if ($request->filled('type_depense') && $request->type_depense !== 'salaire_enseignant') {
            return collect();
        }

        $periode = $this->resolveDateRange($anneeScolaire, $request);

        $query = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereBetween('date_paiement', [$periode['debut'], $periode['fin']]);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->date_fin);
        }

        return $query->with(['enseignant.utilisateur', 'payePar', 'validePar'])
            ->orderBy('date_paiement', 'desc')
            ->get();
    }

    private function mapDepenseToListEntry(Depense $depense): object
    {
        return (object) [
            'id' => 'depense_' . $depense->id,
            'type' => 'depense',
            'date' => $depense->date_depense,
            'libelle' => $depense->libelle,
            'description' => $depense->description,
            'montant' => (float) $depense->montant,
            'type_depense' => $depense->type_depense,
            'approuve_par' => $depense->approuvePar,
            'paye_par' => $depense->payePar,
            'enregistre_par' => $depense->approuvePar ?? $depense->payePar,
            'data' => $depense,
        ];
    }

    private function mapSalaireToListEntry(SalaireEnseignant $salaire): object
    {
        $enseignantNom = $salaire->enseignant && $salaire->enseignant->utilisateur
            ? trim($salaire->enseignant->utilisateur->prenom . ' ' . $salaire->enseignant->utilisateur->nom)
            : 'Enseignant inconnu';

        $periodeDebut = $salaire->periode_debut ? $salaire->periode_debut->format('d/m/Y') : 'N/A';
        $periodeFin = $salaire->periode_fin ? $salaire->periode_fin->format('d/m/Y') : 'N/A';

        return (object) [
            'id' => 'salaire_' . $salaire->id,
            'type' => 'salaire',
            'date' => $salaire->date_paiement,
            'libelle' => 'Salaire - ' . $enseignantNom . ' (' . $periodeDebut . ' - ' . $periodeFin . ')',
            'description' => 'Paiement de salaire pour la période ' . $periodeDebut . ' - ' . $periodeFin,
            'montant' => (float) ($salaire->salaire_net ?? 0),
            'type_depense' => 'salaire_enseignant',
            'approuve_par' => $salaire->validePar ?? null,
            'paye_par' => $salaire->payePar ?? null,
            'enregistre_par' => $salaire->payePar,
            'data' => $salaire,
        ];
    }

    public function sortByDateDesc(Collection $entries): Collection
    {
        return $entries->sort(function ($a, $b) {
            $tsA = $this->entryDateTimestamp($a);
            $tsB = $this->entryDateTimestamp($b);

            if ($tsA !== $tsB) {
                return $tsB <=> $tsA;
            }

            $createdA = isset($a->data->created_at) ? $a->data->created_at->timestamp : 0;
            $createdB = isset($b->data->created_at) ? $b->data->created_at->timestamp : 0;

            return $createdB <=> $createdA;
        })->values();
    }

    private function sortByDateAsc(Collection $entries): Collection
    {
        return $entries->sort(function ($a, $b) {
            $tsA = $this->entryDateTimestamp($a);
            $tsB = $this->entryDateTimestamp($b);

            if ($tsA !== $tsB) {
                return $tsA <=> $tsB;
            }

            $createdA = isset($a->data->created_at) ? $a->data->created_at->timestamp : 0;
            $createdB = isset($b->data->created_at) ? $b->data->created_at->timestamp : 0;

            return $createdA <=> $createdB;
        })->values();
    }

    private function entryDateTimestamp(object $item): int
    {
        if ($item->date instanceof Carbon) {
            return $item->date->timestamp;
        }

        if (is_string($item->date)) {
            return strtotime($item->date) ?: 0;
        }

        return 0;
    }

    private function resolveDateRange(AnneeScolaire $anneeScolaire, Request $request): array
    {
        if ($request->boolean('annee_scolaire_complete')) {
            return $this->strictSchoolYearDateRange($anneeScolaire);
        }

        return [
            'debut' => $anneeScolaire->date_debut->format('Y-m-d'),
            'fin' => $this->resolvePeriodeFin($anneeScolaire),
        ];
    }

    private function resolvePeriodeFin(AnneeScolaire $anneeScolaire): string
    {
        $dateFin = $anneeScolaire->date_fin->copy()->startOfDay();
        $today = Carbon::today();

        if ($anneeScolaire->active && $dateFin->lt($today)) {
            return $today->format('Y-m-d');
        }

        return $dateFin->format('Y-m-d');
    }
}
