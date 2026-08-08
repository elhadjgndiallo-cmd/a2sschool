<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Entree;
use App\Models\Facture;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ComptabiliteEntreesStatsService
{
    private const CACHE_TTL = 180;

    public const SOURCES_SCOLARITE = [
        'Scolarité',
        'Inscription',
        'Réinscription',
        'Transport',
        'Cantine',
        'Uniforme',
        'Livres',
        'Autres frais',
        'Paiements scolaires',
    ];

    /**
     * Somme de toutes les entrées (dons, paiements scolaires, subventions, etc.)
     * sans double comptage entre la table entrees et les paiements.
     */
    public function calculateStats(?Request $request = null, ?AnneeScolaire $anneeScolaire = null): array
    {
        $request = $request ?? new Request();

        if (!$anneeScolaire) {
            return $this->emptyStats();
        }

        if ($this->shouldCacheStats($request)) {
            return Cache::remember(
                'comptabilite_entrees_stats_' . $anneeScolaire->id,
                self::CACHE_TTL,
                fn () => $this->statsFromEntries(
                    $this->buildListEntries($this->requestForYearTotals($request, $anneeScolaire), $anneeScolaire)
                )
            );
        }

        $statsRequest = $this->requestForYearTotals($request, $anneeScolaire);

        return $this->statsFromEntries($this->buildListEntries($statsRequest, $anneeScolaire));
    }

    /**
     * Calcule les stats à partir d'une collection déjà construite (évite une double requête).
     */
    public function statsFromEntries(Collection $entries): array
    {
        $total = (float) $entries->sum('montant');
        $nombre = $entries->count();
        $manuelles = $entries->where('type', 'entree');
        $paiements = $entries->whereIn('type', ['paiement', 'facture']);

        return [
            'total' => $total,
            'nombre' => $nombre,
            'moyenne' => $nombre > 0 ? $total / $nombre : 0,
            'total_manuelles' => (float) $manuelles->sum('montant'),
            'total_paiements' => (float) $paiements->sum('montant'),
            'nombre_manuelles' => $manuelles->count(),
            'nombre_paiements' => $paiements->count(),
        ];
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'nombre' => 0,
            'moyenne' => 0,
            'total_manuelles' => 0,
            'total_paiements' => 0,
            'nombre_manuelles' => 0,
            'nombre_paiements' => 0,
        ];
    }

    private function shouldCacheStats(Request $request): bool
    {
        return !$request->filled('date_debut')
            && !$request->filled('date_fin')
            && !$request->filled('source')
            && !$request->filled('type_entree')
            && !$request->filled('montant_min')
            && !$request->filled('montant_max');
    }

    /**
     * Totaux officiels année scolaire (même source que le rapport annuel).
     */
    public function totauxAnneeScolaireOfficielle(AnneeScolaire $anneeScolaire): array
    {
        $request = $this->requestAnneeScolaireComplete($anneeScolaire);
        $totalEntrees = (float) $this->buildListEntries($request, $anneeScolaire)->sum('montant');
        $totalSorties = (float) app(ComptabiliteSortiesStatsService::class)
            ->buildListEntries($request, $anneeScolaire)
            ->sum('montant');

        return [
            'total_entrees' => $totalEntrees,
            'total_sorties' => $totalSorties,
            'benefice' => $totalEntrees - $totalSorties,
        ];
    }

    /**
     * Somme des totaux mensuels découpés sur l'année scolaire (vérification vs rapport annuel).
     */
    public function totauxMoisAnneeScolaire(AnneeScolaire $anneeScolaire): array
    {
        $sortiesStats = app(ComptabiliteSortiesStatsService::class);
        $anneeDebut = Carbon::parse($anneeScolaire->date_debut)->startOfDay();
        $anneeFin = Carbon::parse($anneeScolaire->date_fin)->endOfDay();

        $totalEntrees = 0.0;
        $totalSorties = 0.0;

        $current = $anneeDebut->copy()->startOfMonth();
        $lastMonth = $anneeFin->copy()->startOfMonth();

        while ($current->lte($lastMonth)) {
            $monthDebut = $current->copy()->startOfMonth()->startOfDay();
            $monthFin = $current->copy()->endOfMonth()->endOfDay();

            $effectiveDebut = $monthDebut->greaterThan($anneeDebut) ? $monthDebut : $anneeDebut->copy();
            $effectiveFin = $monthFin->lessThan($anneeFin) ? $monthFin : $anneeFin->copy();

            if ($effectiveDebut->lte($effectiveFin)) {
                $request = new Request([
                    'date_debut' => $effectiveDebut->format('Y-m-d'),
                    'date_fin' => $effectiveFin->format('Y-m-d'),
                ]);

                $totalEntrees += (float) $this->buildListEntries($request, $anneeScolaire)->sum('montant');
                $totalSorties += (float) $sortiesStats->buildListEntries($request, $anneeScolaire)->sum('montant');
            }

            $current->addMonth();
        }

        return [
            'total_entrees' => $totalEntrees,
            'total_sorties' => $totalSorties,
            'benefice' => $totalEntrees - $totalSorties,
        ];
    }

    /**
     * Requête pour les totaux annuels : période officielle complète de l'année scolaire.
     */
    public function requestAnneeScolaireComplete(AnneeScolaire $anneeScolaire): Request
    {
        return new Request();
    }

    /**
     * Liste unifiée pour comptabilite/entrees, dashboard et statistiques.
     */
    public function buildListEntries(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $query = Entree::with('enregistrePar')
            ->where('annee_scolaire_id', $anneeScolaire->id);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_entree', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_entree', '<=', $request->date_fin);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }

        if ($request->filled('type_entree') && $request->type_entree === 'paiement') {
            $query->whereRaw('1 = 0');
        }

        $entrees = $query->with('enregistrePar:id,nom,prenom,role,photo_profil')
            ->orderByDesc('date_entree')
            ->get();
        $factures = $this->facturesForComptabiliteQuery($request, $anneeScolaire)->get();
        $referencesFacturesPayees = Facture::query()
            ->whereIn('statut', Facture::statutsActifs())
            ->pluck('numero_facture')
            ->flip()
            ->all();

        $paiementsFrais = $this->paiementsFraisForComptabiliteQuery($request, $anneeScolaire)->get();
        $duplicateLookup = $this->buildPaiementDuplicateLookup($paiementsFrais);
        $duplicateLookup['factures'] = $referencesFacturesPayees;

        $allEntries = collect();

        foreach ($entrees as $entree) {
            if ($entree->reference && isset($referencesFacturesPayees[$entree->reference])) {
                continue;
            }

            if ($this->isPaiementDuplicateEntry($entree, $duplicateLookup)) {
                continue;
            }

            if ($request->filled('type_entree') && $request->type_entree === 'paiement') {
                continue;
            }

            $mapped = $this->mapEntreeToListEntry($entree, $request);
            if ($mapped) {
                $allEntries->push($mapped);
            }
        }

        if (!$request->filled('type_entree') || $request->type_entree !== 'manuelle') {
            foreach ($factures as $facture) {
                $entry = $this->mapFactureToListEntry($facture, $request);
                if (!$entry) {
                    continue;
                }

                if ($this->shouldIncludeFactureByDate($entry->date, $request)) {
                    $allEntries->push($entry);
                }
            }

            foreach ($paiementsFrais as $paiement) {
                $entry = $this->mapPaiementToListEntry($paiement, $request);
                if ($entry) {
                    $allEntries->push($entry);
                }
            }
        }

        return $this->sortByDateDesc($allEntries);
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

    private function entryDateTimestamp(object $item): int
    {
        if ($item->date instanceof Carbon) {
            return $item->date->timestamp;
        }

        return strtotime((string) $item->date) ?: 0;
    }

    /**
     * Entrées du rapport journalier (une ligne par facture, sans double comptage).
     */
    public function buildJournalEntrees(Carbon $dateDebut, Carbon $dateFin, AnneeScolaire $anneeScolaire): Collection
    {
        $request = new Request([
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
        ]);

        return $this->buildListEntries($request, $anneeScolaire)
            ->map(fn ($entry) => $this->mapEntryToJournalLine($entry))
            ->values();
    }

    public function mapEntryToJournalLine(object $entry): array
    {
        $libelle = $entry->description;
        if (!empty($entry->detail)) {
            $libelle .= ' — ' . $entry->detail;
        }

        $createdAt = $entry->data->created_at ?? $entry->date;
        $type = match ($entry->type) {
            'facture' => 'facture',
            'paiement' => 'paiement_scolarite',
            default => 'entree_manuelle',
        };

        return [
            'date' => $entry->date,
            'libelle' => $libelle,
            'entree' => (float) $entry->montant,
            'sortie' => 0,
            'type' => $type,
            'source' => $entry->source,
            'enregistre_par' => $entry->enregistre_par,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Collection unifiée (même logique que les listes comptabilite/entrees et entrees).
     */
    public function buildMergedEntries(Request $request, ?AnneeScolaire $anneeScolaire = null): Collection
    {
        if (!$anneeScolaire) {
            return collect();
        }

        return $this->buildListEntries($request, $anneeScolaire)->map(fn ($entry) => (object) [
            'type' => $entry->type,
            'montant' => (float) $entry->montant,
            'source' => $entry->source,
        ]);
    }

    /**
     * Requête optimisée des paiements scolaires (jointure année + eager loading ciblé).
     */
    public function paiementsFraisForComptabiliteQuery(Request $request, AnneeScolaire $anneeScolaire): Builder
    {
        $query = Paiement::query()
            ->sansFacture()
            ->forAnneeScolaire($anneeScolaire->id)
            ->withComptabiliteAffichage();

        if ($request->filled('date_debut')) {
            $query->whereDate('paiements.date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('paiements.date_paiement', '<=', $request->date_fin);
        }

        if ($request->filled('montant_min')) {
            $query->where('paiements.montant_paye', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $query->where('paiements.montant_paye', '<=', $request->montant_max);
        }

        return $query->orderByDesc('paiements.date_paiement');
    }

    /**
     * Factures payées (une entrée comptable par numéro de facture).
     */
    public function facturesForComptabiliteQuery(Request $request, AnneeScolaire $anneeScolaire): Builder
    {
        $query = Facture::query()
            ->whereIn('statut', Facture::statutsActifs())
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->with([
                'eleve.utilisateur:id,nom,prenom',
                'eleve.classe:id,nom',
                'eleve:id,utilisateur_id,classe_id,numero_etudiant',
                'generePar:id,nom,prenom',
                'lignes:id,facture_id,libelle',
            ]);

        if ($request->filled('montant_min')) {
            $query->where('total', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $query->where('total', '<=', $request->montant_max);
        }

        return $query->orderByDesc('date_facture');
    }

    public function dateComptableFacture(Facture $facture): Carbon
    {
        $entree = Entree::query()
            ->where('reference', $facture->numero_facture)
            ->first();

        if ($entree) {
            return $entree->date_entree instanceof Carbon
                ? $entree->date_entree->copy()
                : Carbon::parse($entree->date_entree);
        }

        if ($facture->created_at) {
            return $facture->created_at->copy()->startOfDay();
        }

        return $facture->date_facture instanceof Carbon
            ? $facture->date_facture->copy()
            : Carbon::parse($facture->date_facture);
    }

    /**
     * Filtre par date uniquement pour le journal journalier (même jour début/fin).
     */
    private function shouldIncludeFactureByDate(Carbon|string $date, Request $request): bool
    {
        if (!$request->filled('date_debut') || !$request->filled('date_fin')) {
            return true;
        }

        if ($request->date_debut !== $request->date_fin) {
            return true;
        }

        $dateComptable = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        $debut = Carbon::parse($request->date_debut)->startOfDay();
        $fin = Carbon::parse($request->date_fin)->endOfDay();

        return $dateComptable->between($debut, $fin);
    }

    public function factureEleveResume(Facture $facture): string
    {
        $eleve = $facture->eleve;
        $eleveNom = $eleve?->utilisateur
            ? trim($eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom)
            : 'Élève inconnu';
        $matricule = $eleve?->numero_etudiant ?? 'N/A';
        $classe = $eleve?->classe?->nom ?? 'N/A';

        return $eleveNom . ' (Mat: ' . $matricule . ', Classe: ' . $classe . ')';
    }

    public function mapEntreeToListEntry(Entree $entree, Request $request): ?object
    {
        if ($request->filled('source') && $entree->source !== $request->source) {
            return null;
        }

        return (object) [
            'id' => 'entree_' . $entree->id,
            'type' => 'entree',
            'date' => $entree->date_entree,
            'description' => $entree->description ?: $entree->libelle,
            'detail' => $entree->libelle && $entree->description ? $entree->libelle : null,
            'montant' => (float) $entree->montant,
            'source' => $entree->source,
            'enregistre_par' => $entree->enregistrePar,
            'data' => $entree,
        ];
    }

    public function mapFactureToListEntry(Facture $facture, Request $request): ?object
    {
        if ($request->filled('type_entree') && $request->type_entree === 'manuelle') {
            return null;
        }

        $source = 'Frais de scolarité';
        if ($request->filled('source') && $request->source !== $source) {
            return null;
        }

        $libellesMois = $facture->lignes->pluck('libelle')->implode(', ');

        return (object) [
            'id' => 'facture_' . $facture->id,
            'type' => 'facture',
            'date' => $this->dateComptableFacture($facture),
            'description' => 'Paiement frais scolarité - ' . $this->factureEleveResume($facture),
            'detail' => 'Facture ' . $facture->numero_facture . ' — '
                . number_format((float) $facture->total, 0, ',', ' ') . ' GNF'
                . ($libellesMois ? ' — ' . $libellesMois : ''),
            'montant' => (float) $facture->total,
            'source' => $source,
            'enregistre_par' => $facture->generePar,
            'data' => $facture,
            'reference' => $facture->numero_facture,
        ];
    }

    /**
     * Index O(1) pour détecter les doublons entrées / paiements.
     *
     * @return array{references: array<string, true>, signatures: array<string, true>}
     */
    public function buildPaiementDuplicateLookup(Collection $paiements): array
    {
        $references = [];
        $signatures = [];

        foreach ($paiements as $paiement) {
            if ($paiement->reference_paiement) {
                $references[$paiement->reference_paiement] = true;
            }

            $signatures[$this->paiementDuplicateSignature($paiement)] = true;
        }

        return [
            'references' => $references,
            'signatures' => $signatures,
        ];
    }

    public function paiementDuplicateSignature(Paiement $paiement): string
    {
        return (string) $paiement->montant_paye . '|'
            . $paiement->date_paiement->format('Y-m-d') . '|'
            . $paiement->encaisse_par;
    }

    public function entreeDuplicateSignature(Entree $entree): string
    {
        return (string) $entree->montant . '|'
            . $entree->date_entree->format('Y-m-d') . '|'
            . $entree->enregistre_par;
    }

    /**
     * Description affichée pour une ligne de paiement dans les listes comptabilité.
     */
    public function paiementEleveResume(Paiement $paiement): string
    {
        $eleve = $paiement->fraisScolarite?->eleve;
        $eleveNom = $eleve?->utilisateur
            ? trim($eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom)
            : 'Élève inconnu';
        $matricule = $eleve?->numero_etudiant ?? $eleve?->matricule ?? 'N/A';
        $classe = $eleve?->classe?->nom ?? 'N/A';

        return $eleveNom . ' (Mat: ' . $matricule . ', Classe: ' . $classe . ')';
    }

    public function paiementListDescription(Paiement $paiement): string
    {
        return 'Paiement de ' . number_format((float) $paiement->montant_paye, 0, ',', ' ')
            . ' GNF - ' . $this->paiementEleveResume($paiement);
    }

    public function paiementJournalLibelle(Paiement $paiement): string
    {
        return 'Paiement frais scolarité - ' . $this->paiementEleveResume($paiement);
    }

    /**
     * Convertit un paiement en entrée de liste (null si filtré par la requête).
     */
    public function mapPaiementToListEntry(Paiement $paiement, Request $request): ?object
    {
        $source = $this->sourceFromTypeFrais($paiement->fraisScolarite->type_frais ?? 'autre');

        if ($request->filled('source') && $source !== $request->source) {
            return null;
        }

        if ($request->filled('type_entree') && $request->type_entree === 'manuelle') {
            return null;
        }

        return (object) [
            'id' => 'paiement_' . $paiement->id,
            'type' => 'paiement',
            'date' => $paiement->date_paiement,
            'description' => $this->paiementListDescription($paiement),
            'montant' => $paiement->montant_paye,
            'source' => $source,
            'enregistre_par' => $paiement->encaissePar,
            'data' => $paiement,
        ];
    }

    public function sourceFromTypeFrais(string $typeFrais): string
    {
        $sources = [
            'inscription' => 'Inscription',
            'reinscription' => 'Réinscription',
            'scolarite' => 'Frais de scolarité',
            'cantine' => 'Cantine',
            'transport' => 'Transport',
            'activites' => 'Activités',
            'autre' => 'Autres frais',
        ];

        return $sources[$typeFrais] ?? 'Autres frais';
    }

    public function isPaiementDuplicateEntry(Entree $entree, array $lookup): bool
    {
        if ($entree->reference && isset($lookup['factures'][$entree->reference])) {
            return false;
        }

        if ($entree->reference && isset($lookup['references'][$entree->reference])) {
            return true;
        }

        if (!in_array($entree->source, self::SOURCES_SCOLARITE, true)) {
            return false;
        }

        return isset($lookup['signatures'][$this->entreeDuplicateSignature($entree)]);
    }

    /**
     * Totaux : année scolaire officielle complète sauf si l'utilisateur filtre par dates.
     */
    private function requestForYearTotals(Request $request, AnneeScolaire $anneeScolaire): Request
    {
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            return $request;
        }

        $filters = array_filter($request->only([
            'source',
            'type_entree',
            'montant_min',
            'montant_max',
        ]), fn ($value) => $value !== null && $value !== '');

        return new Request(array_merge(
            $this->requestAnneeScolaireComplete($anneeScolaire)->all(),
            $filters
        ));
    }

    /**
     * Plage de dates pour les listes (extension à aujourd'hui si année active terminée).
     */
    private function resolveDateRange(AnneeScolaire $anneeScolaire, Request $request): array
    {
        if ($request->boolean('annee_scolaire_complete')) {
            return [
                'debut' => $anneeScolaire->date_debut->format('Y-m-d'),
                'fin' => $anneeScolaire->date_fin->format('Y-m-d'),
            ];
        }

        $dateFin = $anneeScolaire->date_fin->copy()->startOfDay();
        $today = Carbon::today();

        if ($anneeScolaire->active && $dateFin->lt($today)) {
            return [
                'debut' => $anneeScolaire->date_debut->format('Y-m-d'),
                'fin' => $today->format('Y-m-d'),
            ];
        }

        return [
            'debut' => $anneeScolaire->date_debut->format('Y-m-d'),
            'fin' => $anneeScolaire->date_fin->format('Y-m-d'),
        ];
    }
}
