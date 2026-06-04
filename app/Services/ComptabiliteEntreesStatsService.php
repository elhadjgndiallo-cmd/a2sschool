<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Entree;
use App\Models\Paiement;
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

        $paiementsFraisQuery = Paiement::with('fraisScolarite:id,type_frais,eleve_id')
            ->whereHas('fraisScolarite.eleve', function ($q) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $q->where('annee_scolaire_id', $anneeScolaire->id);
                }
            });

        if ($request->filled('date_debut')) {
            $paiementsFraisQuery->whereDate('date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $paiementsFraisQuery->whereDate('date_paiement', '<=', $request->date_fin);
        }

        if ($request->filled('montant_min')) {
            $paiementsFraisQuery->where('montant_paye', '>=', $request->montant_min);
        }

        if ($request->filled('montant_max')) {
            $paiementsFraisQuery->where('montant_paye', '<=', $request->montant_max);
        }

        $paiementsFrais = $paiementsFraisQuery->orderBy('date_paiement', 'desc')->get();
        $paiementsReferences = $paiementsFrais->pluck('reference_paiement')->filter()->toArray();

        $allEntries = collect();

        foreach ($entrees as $entree) {
            if ($this->isPaiementDuplicateEntry($entree, $paiementsFrais, $paiementsReferences)) {
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

    public function isPaiementDuplicateEntry(Entree $entree, Collection $paiementsFrais, array $paiementsReferences): bool
    {
        if ($entree->reference && in_array($entree->reference, $paiementsReferences, true)) {
            return true;
        }

        if (!in_array($entree->source, self::SOURCES_SCOLARITE, true)) {
            return false;
        }

        foreach ($paiementsFrais as $paiement) {
            if ($paiement->montant_paye == $entree->montant
                && $paiement->date_paiement->format('Y-m-d') === $entree->date_entree->format('Y-m-d')
                && $paiement->encaisse_par == $entree->enregistre_par) {
                return true;
            }
        }

        return false;
    }
}
