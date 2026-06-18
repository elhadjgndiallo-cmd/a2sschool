<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Entree;
use App\Models\Paiement;
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
        $merged = $this->buildMergedEntries($request, $anneeScolaire);

        $total = (float) $merged->sum('montant');
        $nombre = $merged->count();
        $manuelles = $merged->where('type', 'entree');
        $paiements = $merged->where('type', 'paiement');

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
     * Collection unifiée (même logique que les listes comptabilite/entrees et entrees).
     */
    public function buildMergedEntries(Request $request, ?AnneeScolaire $anneeScolaire = null): Collection
    {
        $query = Entree::query();

        if ($anneeScolaire) {
            $query->whereBetween('date_entree', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d'),
            ]);
        }

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

        $entrees = $query->orderBy('date_entree', 'desc')->get();

        $paiementsFrais = $anneeScolaire
            ? $this->paiementsFraisForComptabiliteQuery($request, $anneeScolaire)->get()
            : collect();

        $duplicateLookup = $this->buildPaiementDuplicateLookup($paiementsFrais);

        $allEntries = collect();

        foreach ($entrees as $entree) {
            if ($this->isPaiementDuplicateEntry($entree, $duplicateLookup)) {
                continue;
            }

            $allEntries->push((object) [
                'type' => 'entree',
                'montant' => (float) $entree->montant,
                'source' => $entree->source,
            ]);
        }

        if ($anneeScolaire && (!$request->filled('type_entree') || $request->type_entree !== 'manuelle')) {
            foreach ($paiementsFrais as $paiement) {
                $source = $this->sourceFromTypeFrais($paiement->fraisScolarite->type_frais ?? 'autre');

                if ($request->filled('source') && $source !== $request->source) {
                    continue;
                }

                $allEntries->push((object) [
                    'type' => 'paiement',
                    'montant' => (float) $paiement->montant_paye,
                    'source' => $source,
                ]);
            }
        }

        return $allEntries;
    }

    /**
     * Requête optimisée des paiements scolaires (jointure année + eager loading ciblé).
     */
    public function paiementsFraisForComptabiliteQuery(Request $request, AnneeScolaire $anneeScolaire): Builder
    {
        $query = Paiement::query()
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

        // Paiements rattachés à une facture : une seule entrée comptable globale
        $query->whereNotExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('facture_lignes')
                ->whereColumn('facture_lignes.paiement_id', 'paiements.id');
        });

        return $query->orderByDesc('paiements.date_paiement');
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
        if ($entree->reference && isset($lookup['references'][$entree->reference])) {
            return true;
        }

        if (!in_array($entree->source, self::SOURCES_SCOLARITE, true)) {
            return false;
        }

        return isset($lookup['signatures'][$this->entreeDuplicateSignature($entree)]);
    }
}
