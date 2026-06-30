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

class ComptabiliteEntreesStatsService
{
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

        $statsRequest = $this->requestForYearTotals($request, $anneeScolaire);
        $merged = $this->buildMergedEntries($statsRequest, $anneeScolaire);

        $total = (float) $merged->sum('montant');
        $nombre = $merged->count();
        $manuelles = $merged->where('type', 'entree');
        $paiements = $merged->whereIn('type', ['paiement', 'facture']);

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
     * Requête pour les totaux annuels : période officielle complète de l'année scolaire.
     */
    public function requestAnneeScolaireComplete(AnneeScolaire $anneeScolaire): Request
    {
        return new Request([
            'date_debut' => $anneeScolaire->date_debut->format('Y-m-d'),
            'date_fin' => $anneeScolaire->date_fin->format('Y-m-d'),
            'annee_scolaire_complete' => true,
        ]);
    }

    /**
     * Liste unifiée pour comptabilite/entrees, dashboard et statistiques.
     */
    public function buildListEntries(Request $request, AnneeScolaire $anneeScolaire): Collection
    {
        $periode = $this->resolveDateRange($anneeScolaire, $request);
        $query = Entree::with('enregistrePar');

        $query->whereBetween('date_entree', [$periode['debut'], $periode['fin']]);

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

        $entrees = $query->orderByDesc('date_entree')->get();
        $factures = $this->facturesForComptabiliteQuery($request, $anneeScolaire)->get();
        $numerosFactures = $factures->pluck('numero_facture')->flip()->all();

        $paiementsFrais = $this->paiementsFraisForComptabiliteQuery($request, $anneeScolaire)->get();
        $duplicateLookup = $this->buildPaiementDuplicateLookup($paiementsFrais);

        $allEntries = collect();

        foreach ($entrees as $entree) {
            if ($entree->reference && isset($numerosFactures[$entree->reference])) {
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
                if ($entry) {
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

        return $allEntries->sort(function ($a, $b) {
            $tsA = $a->date instanceof \Carbon\Carbon ? $a->date->timestamp : strtotime((string) $a->date);
            $tsB = $b->date instanceof \Carbon\Carbon ? $b->date->timestamp : strtotime((string) $b->date);

            if ($tsA !== $tsB) {
                return $tsA <=> $tsB;
            }

            $createdA = isset($a->data->created_at) ? $a->data->created_at->timestamp : 0;
            $createdB = isset($b->data->created_at) ? $b->data->created_at->timestamp : 0;

            return $createdA <=> $createdB;
        })->values();
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
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        $query = Paiement::query()
            ->sansFacture()
            ->forAnneeScolaire($anneeScolaire->id)
            ->withComptabiliteAffichage()
            ->whereBetween('paiements.date_paiement', [$periode['debut'], $periode['fin']]);

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
        $periode = $this->resolveDateRange($anneeScolaire, $request);

        $query = Facture::query()
            ->where('statut', 'payee')
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->whereBetween('date_facture', [$periode['debut'], $periode['fin']])
            ->with([
                'eleve.utilisateur:id,nom,prenom',
                'eleve.classe:id,nom',
                'eleve:id,utilisateur_id,classe_id,numero_etudiant',
                'generePar:id,nom,prenom',
                'lignes:id,facture_id,libelle',
            ]);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }

        if ($request->filled('montant_min')) {
            $query->where('total', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $query->where('total', '<=', $request->montant_max);
        }

        return $query->orderByDesc('date_facture');
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
            'date' => $facture->date_facture,
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
        if ($entree->reference && Facture::where('numero_facture', $entree->reference)->exists()) {
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
