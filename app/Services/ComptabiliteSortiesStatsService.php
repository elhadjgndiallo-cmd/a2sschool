<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Depense;
use App\Models\SalaireEnseignant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ComptabiliteSortiesStatsService
{
    private const CACHE_TTL = 180;

    /**
     * Total des sorties : dépenses (hors doublons salaires) + salaires enseignants payés.
     */
    public function calculateStats(?Request $request = null, ?AnneeScolaire $anneeScolaire = null): array
    {
        $request = $request ?? new Request();

        if (!$anneeScolaire) {
            return $this->emptyStats();
        }

        if ($this->shouldCacheStats($request)) {
            return Cache::remember(
                'comptabilite_sorties_stats_' . $anneeScolaire->id,
                self::CACHE_TTL,
                fn () => $this->computeStatsFast($request, $anneeScolaire)
            );
        }

        return $this->statsFromEntries($this->buildListEntries($request, $anneeScolaire));
    }

    /**
     * Calcule les stats à partir d'une collection déjà construite (évite une double requête).
     */
    public function statsFromEntries(Collection $entries): array
    {
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

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'nombre' => 0,
            'moyenne' => 0,
            'total_depenses' => 0,
            'total_salaires' => 0,
        ];
    }

    private function shouldCacheStats(Request $request): bool
    {
        return !$request->filled('date_debut')
            && !$request->filled('date_fin')
            && !$request->filled('type_depense');
    }

    /**
     * Totaux via agrégats SQL (sans charger toutes les lignes).
     */
    private function computeStatsFast(Request $request, AnneeScolaire $anneeScolaire): array
    {
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        $totalSalaires = (float) $this->baseSalairesQuery($request, $anneeScolaire, $periode)
            ->sum('salaire_net');

        $nombreSalaires = $this->baseSalairesQuery($request, $anneeScolaire, $periode)->count();

        $dedupLookup = $this->buildSalaryDedupLookup(
            $this->baseSalairesQuery($request, $anneeScolaire, $periode)
                ->get(['date_paiement', 'salaire_net'])
        );

        $totalDepensesNormales = (float) $this->baseDepensesQuery($request, $anneeScolaire, $periode)
            ->where('type_depense', '!=', 'salaire_enseignant')
            ->sum('montant');

        $nombreDepensesNormales = $this->baseDepensesQuery($request, $anneeScolaire, $periode)
            ->where('type_depense', '!=', 'salaire_enseignant')
            ->count();

        $depensesSalaireManuelles = $this->baseDepensesQuery($request, $anneeScolaire, $periode)
            ->where('type_depense', 'salaire_enseignant')
            ->get();

        $totalSalairesManuels = 0.0;
        $nombreSalairesManuels = 0;

        foreach ($depensesSalaireManuelles as $depense) {
            if ($this->depenseLieeAuModuleSalaires($depense, $dedupLookup)) {
                continue;
            }

            $totalSalairesManuels += (float) $depense->montant;
            $nombreSalairesManuels++;
        }

        $totalDepenses = $totalDepensesNormales + $totalSalairesManuels;
        $total = $totalDepenses + $totalSalaires;
        $nombre = $nombreDepensesNormales + $nombreSalairesManuels + $nombreSalaires;

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
        $dedupLookup = $this->buildSalaryDedupLookup($salairesPayes);

        $allSorties = collect();

        foreach ($depenses as $depense) {
            if ($this->depenseLieeAuModuleSalaires($depense, $dedupLookup)) {
                continue;
            }

            $allSorties->push($this->mapDepenseToListEntry($depense));
        }

        foreach ($salairesPayes as $salaire) {
            $allSorties->push($this->mapSalaireToListEntry($salaire));
        }

        return $this->sortByDateDesc($allSorties);
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

        return $this->salaryDedupKey(
            $depense->date_depense->format('Y-m-d'),
            (float) $depense->montant
        ) === $this->salaryDedupKey(
            $salaire->date_paiement->format('Y-m-d'),
            (float) $salaire->salaire_net
        );
    }

    /**
     * Index O(1) pour la déduplication dépense ↔ salaire payé.
     *
     * @return array<string, true>
     */
    public function buildSalaryDedupLookup(Collection $salairesPayes): array
    {
        $lookup = [];

        foreach ($salairesPayes as $salaire) {
            if (!$salaire->date_paiement) {
                continue;
            }

            $lookup[$this->salaryDedupKey(
                $salaire->date_paiement->format('Y-m-d'),
                (float) $salaire->salaire_net
            )] = true;
        }

        return $lookup;
    }

    private function salaryDedupKey(string $date, float $montant): string
    {
        return $date . '|' . number_format($montant, 2, '.', '');
    }

    /**
     * Dépense salaire déjà comptée via le module salaires (à exclure des sorties manuelles).
     */
    public function depenseLieeAuModuleSalaires(Depense $depense, array $dedupLookup = []): bool
    {
        if ($depense->type_depense !== 'salaire_enseignant') {
            return false;
        }

        if (Depense::hasSalaireEnseignantLinkColumn() && $depense->salaire_enseignant_id) {
            return true;
        }

        if ($dedupLookup !== []) {
            return $this->isDepenseInSalaryLookup($depense, $dedupLookup);
        }

        return false;
    }

    private function isDepenseSalaireEnseignant(Depense $depense): bool
    {
        return $depense->type_depense === 'salaire_enseignant';
    }

    private function isDepenseInSalaryLookup(Depense $depense, array $lookup): bool
    {
        if ($depense->type_depense !== 'salaire_enseignant' || !$depense->date_depense) {
            return false;
        }

        return isset($lookup[$this->salaryDedupKey(
            $depense->date_depense->format('Y-m-d'),
            (float) $depense->montant
        )]);
    }

    private function baseDepensesQuery(Request $request, AnneeScolaire $anneeScolaire, array $periode)
    {
        $query = Depense::query()
            ->where('statut', '!=', 'annule')
            ->where('annee_scolaire_id', $anneeScolaire->id);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }

        return $query;
    }

    private function baseSalairesQuery(Request $request, AnneeScolaire $anneeScolaire, array $periode)
    {
        if ($request->filled('type_depense') && $request->type_depense !== 'salaire_enseignant') {
            return SalaireEnseignant::whereRaw('1 = 0');
        }

        $query = SalaireEnseignant::where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->whereHas('enseignant', fn ($q) => $q->where('annee_scolaire_id', $anneeScolaire->id));

        if ($request->filled('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->date_fin);
        }

        return $query;
    }

    private function fetchDepenses(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        return $this->baseDepensesQuery($request, $anneeScolaire, $periode)
            ->with([
                'approuvePar:id,nom,prenom,role,photo_profil',
                'payePar:id,nom,prenom,role,photo_profil',
            ])
            ->orderBy('date_depense', 'desc')
            ->get();
    }

    private function fetchSalairesPayes(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        return $this->baseSalairesQuery($request, $anneeScolaire, $periode)
            ->with([
                'enseignant:id,utilisateur_id',
                'enseignant.utilisateur:id,nom,prenom',
                'payePar:id,nom,prenom,role,photo_profil',
                'validePar:id,nom,prenom,role,photo_profil',
            ])
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
