<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\FraisScolarite;
use App\Models\TarifClasse;
use App\Models\TranchePaiement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacturationService
{
    private const TYPES_MENSUELS = ['scolarite', 'cantine', 'transport'];

    private const LABELS_TYPE = [
        'scolarite' => 'Scolarité',
        'cantine' => 'Cantine',
        'transport' => 'Transport',
    ];

    public function __construct(
        private PaiementScolariteService $paiementScolariteService
    ) {}

    public function getLignesDisponibles(Eleve $eleve, ?AnneeScolaire $anneeScolaire = null): array
    {
        $anneeScolaire = $anneeScolaire ?? AnneeScolaire::anneeActive();
        if (!$anneeScolaire) {
            return [];
        }

        $eleve->loadMissing(['classe']);
        $lignes = collect();

        $moisCourant = now()->startOfMonth();
        $debutPeriode = Carbon::parse($anneeScolaire->date_debut)->startOfMonth();

        $fraisList = FraisScolarite::where('eleve_id', $eleve->id)
            ->whereIn('type_frais', self::TYPES_MENSUELS)
            ->where('statut', '!=', 'annule')
            ->with(['tranchesPaiement' => fn ($q) => $q->orderBy('numero_tranche')])
            ->get();

        foreach ($fraisList as $frais) {
            $this->realignerTranchesSiNecessaire($frais, $anneeScolaire);
        }
        $fraisList->load(['tranchesPaiement' => fn ($q) => $q->orderBy('numero_tranche')]);

        $tarif = $this->getTarifClasse($eleve, $anneeScolaire);
        if (!$tarif) {
            return [];
        }

        foreach (self::TYPES_MENSUELS as $type) {
            $montantMensuel = $this->montantMensuelTarif($tarif, $type);
            if ($montantMensuel <= 0) {
                continue;
            }

            $frais = $fraisList->firstWhere('type_frais', $type);
            $nombreMois = (int) ($frais?->nombre_tranches ?? $tarif->nombre_tranches);

            foreach ($this->moisPeriodeTranches($debutPeriode, $nombreMois) as $mois) {
                // Mois futurs : pas de paiement anticipé
                if ($mois->gt($moisCourant)) {
                    continue;
                }

                $tranche = $frais?->tranchesPaiement->first(
                    fn (TranchePaiement $t) => Carbon::parse($t->date_echeance)->format('Y-m') === $mois->format('Y-m')
                );

                if ($tranche) {
                    $montantTranche = (float) $tranche->montant_tranche;
                    $reste = round($montantTranche - (float) $tranche->montant_paye, 2);

                    if ($tranche->statut === 'paye' || $reste <= 0) {
                        continue;
                    }

                    $libelle = $this->libelleLigne($type, $mois);
                    if ($reste + 0.00001 < $montantTranche) {
                        $libelle .= ' (reste ' . number_format($reste, 0, ',', ' ') . ' GNF)';
                    }

                    $lignes->push($this->formatLigne([
                        'id' => 'tranche:' . $tranche->id,
                        'source' => 'tranche',
                        'type_frais' => $type,
                        'mois' => $mois->format('Y-m-d'),
                        'libelle' => $libelle,
                        'montant' => $reste,
                        'montant_du_mois' => round($montantTranche, 2),
                        'partiel' => $reste + 0.00001 < $montantTranche,
                        'tranche_id' => $tranche->id,
                        'frais_id' => $frais->id,
                    ]));
                } else {
                    $lignes->push($this->formatLigne([
                        'id' => 'tarif:' . $type . ':' . $mois->format('Y-m'),
                        'source' => 'tarif',
                        'type_frais' => $type,
                        'mois' => $mois->format('Y-m-d'),
                        'libelle' => $this->libelleLigne($type, $mois),
                        'montant' => round($montantMensuel, 2),
                        'montant_du_mois' => round($montantMensuel, 2),
                        'partiel' => false,
                        'tranche_id' => null,
                        'frais_id' => $frais?->id,
                    ]));
                }
            }
        }

        return $lignes
            ->sortBy([['mois', 'asc'], ['type_frais', 'asc']])
            ->values()
            ->all();
    }

    public function aFraisImpayes(Eleve $eleve, ?AnneeScolaire $anneeScolaire = null): bool
    {
        if ($eleve->exempte_frais) {
            return false;
        }

        return count($this->getLignesDisponibles($eleve, $anneeScolaire)) > 0;
    }

    /**
     * @param  array<int, array{id: string, montant: float}>  $lignesSelection
     * @return array{sous_total: float, montant_remise: float, total: float, lignes: array<int, array<string, mixed>>}
     */
    public function calculerTotaux(array $lignesSelection, string $remiseType, float $remiseValeur): array
    {
        $sousTotal = round(collect($lignesSelection)->sum('montant'), 2);
        $montantRemise = $this->calculerMontantRemise($sousTotal, $remiseType, $remiseValeur);
        $total = max(0, round($sousTotal - $montantRemise, 2));
        $lignesAvecDu = $this->lignesAvecMontantDu($lignesSelection, $montantRemise);

        return [
            'sous_total' => $sousTotal,
            'montant_remise' => $montantRemise,
            'total' => $total,
            'lignes' => $lignesAvecDu,
        ];
    }

    /**
     * Applique la remise globale sur les derniers mois (sans répartir sur chaque ligne).
     * Ex. remise 4 400 sur Nov 9 600 + Déc 74 800 → Déc dû = 70 400, Nov = 9 600.
     *
     * @param  array<int, array<string, mixed>>  $lignesSelection
     * @return array<int, array<string, mixed>>
     */
    private function lignesAvecMontantDu(array $lignesSelection, float $montantRemise): array
    {
        $lignesTriees = collect($lignesSelection)->sortBy('mois')->values()->all();
        $montantsDu = array_map(
            fn (array $ligne) => round((float) $ligne['montant'], 2),
            $lignesTriees
        );

        $remiseRestante = round($montantRemise, 2);
        for ($i = count($lignesTriees) - 1; $i >= 0 && $remiseRestante > 0.001; $i--) {
            $deduction = min($remiseRestante, $montantsDu[$i]);
            $montantsDu[$i] = round($montantsDu[$i] - $deduction, 2);
            $remiseRestante = round($remiseRestante - $deduction, 2);
        }

        $result = [];
        foreach ($lignesTriees as $i => $ligne) {
            $brut = round((float) $ligne['montant'], 2);
            $du = $montantsDu[$i];

            $result[] = array_merge($ligne, [
                'montant_brut' => $brut,
                'montant_du' => $du,
                'remise_ligne' => round($brut - $du, 2),
                'montant_remise' => 0,
                'montant_net' => $du,
            ]);
        }

        return $result;
    }

    /**
     * Calcule les totaux avec un montant versé pouvant être inférieur au total dû
     * (paiement partiel ou avance sur le mois suivant).
     *
     * @param  array<int, array<string, mixed>>  $lignesSelection
     * @return array{sous_total: float, montant_remise: float, total_du: float, montant_verse: float, total: float, reste_a_payer: float, lignes: array<int, array<string, mixed>>}
     */
    public function calculerTotauxAvecVersement(
        array $lignesSelection,
        string $remiseType,
        float $remiseValeur,
        float $montantVerse
    ): array {
        $lignesTriees = collect($lignesSelection)->sortBy('mois')->values()->all();
        $totaux = $this->calculerTotaux($lignesTriees, $remiseType, $remiseValeur);
        $totalDu = $totaux['total'];

        if ($totalDu <= 0 && $totaux['sous_total'] > 0) {
            throw new \RuntimeException('La remise ne peut pas couvrir la totalité de la facture.');
        }

        $montantVerse = round($montantVerse, 2);
        if ($montantVerse <= 0) {
            throw new \RuntimeException('Le montant versé doit être supérieur à zéro.');
        }

        if ($montantVerse > $totalDu + 0.01) {
            throw new \RuntimeException(
                'Le montant versé (' . number_format($montantVerse, 0, ',', ' ')
                . ' GNF) dépasse le total dû (' . number_format($totalDu, 0, ',', ' ') . ' GNF).'
            );
        }

        $reste = $montantVerse;
        $lignesPayees = [];

        foreach ($totaux['lignes'] as $ligne) {
            if ($reste <= 0) {
                break;
            }

            $du = round((float) ($ligne['montant_du'] ?? $ligne['montant_net'] ?? $ligne['montant']), 2);
            if ($du <= 0) {
                continue;
            }

            $paye = round(min($du, $reste), 2);

            if ($paye <= 0) {
                continue;
            }

            $resteLigne = round($du - $paye, 2);
            $partiel = $resteLigne > 0.01;
            $libelle = preg_replace('/\s*\((?:reste|partiel)[^)]*\)/', '', $ligne['libelle'] ?? '') ?? ($ligne['libelle'] ?? '');

            if ($partiel) {
                $libelle .= ' (partiel ' . number_format($paye, 0, ',', ' ') . ' / ' . number_format($du, 0, ',', ' ') . ' GNF)';
            } elseif ($resteLigne > 0.01) {
                $libelle .= ' (reste ' . number_format($resteLigne, 0, ',', ' ') . ' GNF)';
            }

            $lignesPayees[] = array_merge($ligne, [
                'libelle' => $libelle,
                'montant_brut' => $paye,
                'montant_remise' => 0,
                'montant_net' => $paye,
                'remise_ligne' => (float) ($ligne['remise_ligne'] ?? 0),
                'reste' => $resteLigne,
                'partiel' => $partiel,
            ]);

            $reste = round($reste - $paye, 2);
        }

        // Mois non touchés par le paiement mais encore dus
        $idsPayes = collect($lignesPayees)->pluck('id')->all();
        foreach ($totaux['lignes'] as $ligne) {
            if (in_array($ligne['id'] ?? null, $idsPayes, true)) {
                continue;
            }

            $du = round((float) ($ligne['montant_du'] ?? $ligne['montant_net'] ?? 0), 2);
            if ($du <= 0) {
                continue;
            }

            $libelle = preg_replace('/\s*\((?:reste|partiel)[^)]*\)/', '', $ligne['libelle'] ?? '') ?? ($ligne['libelle'] ?? '');
            $lignesPayees[] = array_merge($ligne, [
                'libelle' => $libelle,
                'montant_brut' => 0,
                'montant_remise' => 0,
                'montant_net' => 0,
                'reste' => $du,
                'partiel' => false,
                'non_paye' => true,
            ]);
        }

        return [
            'sous_total' => $totaux['sous_total'],
            'montant_remise' => $totaux['montant_remise'],
            'total_du' => $totalDu,
            'montant_verse' => $montantVerse,
            'total' => $montantVerse,
            'reste_a_payer' => max(0, round($totalDu - $montantVerse, 2)),
            'lignes' => $lignesPayees,
        ];
    }

    /**
     * Répartit un montant versé sur les mois impayés (FIFO), avec paiement partiel possible.
     * Ex. 300 000 GNF sur des mois à 120 000 → 120 000 + 120 000 + 60 000 (partiel).
     *
     * @return array<int, array<string, mixed>>
     */
    public function repartirMontantSurMois(
        Eleve $eleve,
        string $typeFrais,
        float $montantNet,
        ?AnneeScolaire $anneeScolaire = null
    ): array {
        if ($montantNet <= 0) {
            throw new \RuntimeException('Le montant versé doit être supérieur à zéro.');
        }

        if (!in_array($typeFrais, self::TYPES_MENSUELS, true)) {
            throw new \RuntimeException('Type de frais invalide pour la répartition.');
        }

        $lignes = collect($this->getLignesDisponibles($eleve, $anneeScolaire))
            ->where('type_frais', $typeFrais)
            ->sortBy('mois')
            ->values();

        if ($lignes->isEmpty()) {
            throw new \RuntimeException('Aucun mois impayé disponible pour ce type de frais.');
        }

        $reste = round($montantNet, 2);
        $allocation = [];

        foreach ($lignes as $ligne) {
            if ($reste <= 0) {
                break;
            }

            $du = round((float) $ligne['montant'], 2);
            $verse = round(min($du, $reste), 2);

            if ($verse <= 0) {
                continue;
            }

            $partiel = $verse + 0.00001 < $du;
            $libelle = $ligne['libelle'];
            if ($partiel) {
                $libelle .= ' (partiel ' . number_format($verse, 0, ',', ' ') . ' / ' . number_format($du, 0, ',', ' ') . ' GNF)';
            }

            $allocation[] = [
                'id' => $ligne['id'],
                'source' => $ligne['source'],
                'type_frais' => $ligne['type_frais'],
                'mois' => $ligne['mois'],
                'libelle' => $libelle,
                'montant' => $verse,
                'montant_du_mois' => (float) ($ligne['montant_du_mois'] ?? $du),
                'partiel' => $partiel,
                'tranche_id' => $ligne['tranche_id'],
                'frais_id' => $ligne['frais_id'],
            ];

            $reste = round($reste - $verse, 2);
        }

        if ($reste > 0.01) {
            throw new \RuntimeException(
                'Le montant versé dépasse les frais dus pour « ' . (self::LABELS_TYPE[$typeFrais] ?? $typeFrais)
                . ' ». Surplus : ' . number_format($reste, 0, ',', ' ') . ' GNF. '
                . 'Réduisez le montant ou attendez que de nouveaux mois soient ouverts.'
            );
        }

        return $allocation;
    }

    /**
     * Aperçu de la répartition pour le mode « montant versé ».
     */
    public function previewRepartitionMontant(
        Eleve $eleve,
        string $typeFrais,
        float $montantVerse,
        string $remiseType,
        float $remiseValeur
    ): array {
        $montantVerse = round($montantVerse, 2);
        $montantRemise = $this->calculerMontantRemise($montantVerse, $remiseType, $remiseValeur);
        $montantNet = max(0, round($montantVerse - $montantRemise, 2));

        $lignes = $this->repartirMontantSurMois($eleve, $typeFrais, $montantNet);

        return [
            'sous_total' => $montantVerse,
            'montant_remise' => $montantRemise,
            'total' => $montantNet,
            'lignes' => array_map(fn ($l) => [
                'libelle' => $l['libelle'],
                'montant' => $l['montant'],
                'partiel' => $l['partiel'],
            ], $lignes),
        ];
    }

    public function emettreFacture(array $data): Facture
    {
        $eleve = Eleve::with('classe')->findOrFail($data['eleve_id']);
        $anneeScolaire = AnneeScolaire::anneeActive();

        if (!$anneeScolaire) {
            throw new \RuntimeException('Aucune année scolaire active.');
        }

        if ($eleve->exempte_frais) {
            throw new \RuntimeException('Cet élève est exempté de frais de scolarité.');
        }

        if ($eleve->annee_scolaire_id !== $anneeScolaire->id) {
            throw new \RuntimeException('L\'élève n\'appartient pas à l\'année scolaire active.');
        }

        $preparation = $this->preparerDonneesEmission($data, $eleve, $anneeScolaire);

        return DB::transaction(function () use ($data, $eleve, $anneeScolaire, $preparation) {
            $facture = Facture::create([
                'eleve_id' => $eleve->id,
                'annee_scolaire_id' => $anneeScolaire->id,
                'date_facture' => $data['date_facture'],
                'date_echeance' => $data['date_echeance'] ?? null,
                'sous_total' => $preparation['totaux']['sous_total'],
                'remise_type' => $data['remise_type'],
                'remise_valeur' => $data['remise_valeur'],
                'montant_remise' => $preparation['totaux']['montant_remise'],
                'total' => $preparation['totaux']['total'],
                'mode_paiement' => $data['mode_paiement'],
                'reference_paiement' => $preparation['reference'],
                'observations' => $preparation['observations'],
                'statut' => 'payee',
                'genere_par' => auth()->id(),
            ]);

            $this->enregistrerLignesEtPaiementsFacture(
                $facture,
                $eleve,
                $anneeScolaire,
                $preparation['tarif'],
                $preparation['totaux'],
                $data,
                $facture->numero_facture,
                $preparation['observations']
            );

            $this->paiementScolariteService->creerEntreeComptableFacture($facture);

            return $facture->load(['lignes', 'eleve.utilisateur', 'eleve.classe', 'generePar']);
        });
    }

    public function getLignesPourEditionFacture(Facture $facture): array
    {
        $facture->loadMissing(['lignes.tranchePaiement', 'eleve', 'anneeScolaire']);
        $disponibles = collect($this->getLignesDisponibles($facture->eleve, $facture->anneeScolaire))->keyBy('id');

        foreach ($facture->lignes as $ligne) {
            $id = $this->ligneIdFromFactureLigne($ligne);
            $mois = Carbon::parse($ligne->mois);
            $libelle = preg_replace('/\s*\([^)]*\)\s*$/', '', $ligne->libelle) ?: $ligne->libelle;
            $montant = $this->montantLigneFacturePourEdition($ligne, $disponibles->get($id));

            if ($disponibles->has($id)) {
                $existing = $disponibles->get($id);
                $disponibles->put($id, $this->formatLigne(array_merge($existing, [
                    'libelle' => $libelle,
                    'montant' => $montant,
                    'montant_du_mois' => (float) ($existing['montant_du_mois'] ?? $montant),
                    'facture_actuelle' => true,
                ])));

                continue;
            }

            $disponibles->put($id, $this->formatLigne([
                'id' => $id,
                'source' => $ligne->tranche_paiement_id ? 'tranche' : 'tarif',
                'type_frais' => $ligne->type_frais,
                'mois' => $mois->format('Y-m-d'),
                'libelle' => $libelle,
                'montant' => $montant,
                'montant_du_mois' => $montant,
                'partiel' => false,
                'tranche_id' => $ligne->tranche_paiement_id,
                'frais_id' => $ligne->frais_scolarite_id,
                'facture_actuelle' => true,
            ]));
        }

        return $disponibles->values()
            ->sortBy([['mois', 'asc'], ['type_frais', 'asc']])
            ->values()
            ->all();
    }

    public function getLignesSelectionIdsFromFacture(Facture $facture): array
    {
        $facture->loadMissing('lignes');

        return $facture->lignes
            ->map(fn (FactureLigne $ligne) => $this->ligneIdFromFactureLigne($ligne))
            ->unique()
            ->values()
            ->all();
    }

    public function supprimerFacture(Facture $facture): void
    {
        if ($facture->statut !== 'payee') {
            throw new \RuntimeException('Cette facture est déjà annulée.');
        }

        DB::transaction(function () use ($facture) {
            $this->annulerEffetsFacture($facture);
            $facture->delete();
        });
    }

    public function modifierFacture(Facture $facture, array $data): Facture
    {
        if ($facture->statut !== 'payee') {
            throw new \RuntimeException('Seules les factures payées peuvent être modifiées.');
        }

        $eleve = Eleve::with('classe')->findOrFail($facture->eleve_id);
        $anneeScolaire = $facture->anneeScolaire ?? AnneeScolaire::find($facture->annee_scolaire_id);

        if (!$anneeScolaire) {
            throw new \RuntimeException('Année scolaire introuvable pour cette facture.');
        }

        if ((int) ($data['eleve_id'] ?? $eleve->id) !== (int) $facture->eleve_id) {
            throw new \RuntimeException('Impossible de changer l\'élève d\'une facture existante.');
        }

        return DB::transaction(function () use ($facture, $eleve, $anneeScolaire, $data) {
            // Annuler d'abord les paiements pour recalculer sur les vrais restes dus
            $this->annulerEffetsFacture($facture);

            $preparation = $this->preparerDonneesEmission($data, $eleve, $anneeScolaire);

            $facture->update([
                'date_facture' => $preparation['data']['date_facture'],
                'date_echeance' => $preparation['data']['date_echeance'] ?? null,
                'sous_total' => $preparation['totaux']['sous_total'],
                'remise_type' => $preparation['data']['remise_type'],
                'remise_valeur' => $preparation['data']['remise_valeur'],
                'montant_remise' => $preparation['totaux']['montant_remise'],
                'total' => $preparation['totaux']['total'],
                'mode_paiement' => $preparation['data']['mode_paiement'],
                'reference_paiement' => $preparation['reference'],
                'observations' => $preparation['observations'],
                'statut' => 'payee',
            ]);

            $this->enregistrerLignesEtPaiementsFacture(
                $facture,
                $eleve,
                $anneeScolaire,
                $preparation['tarif'],
                $preparation['totaux'],
                $preparation['data'],
                $facture->numero_facture,
                $preparation['observations']
            );

            $this->paiementScolariteService->mettreAJourEntreeComptableFacture($facture->fresh(['lignes']));

            return $facture->fresh(['lignes', 'eleve.utilisateur', 'eleve.classe', 'generePar']);
        });
    }

    private function annulerEffetsFacture(Facture $facture): void
    {
        $facture->load(['lignes.paiement', 'lignes.tranchePaiement', 'lignes.fraisScolarite']);

        foreach ($facture->lignes as $ligne) {
            $this->paiementScolariteService->annulerPaiementFactureLigne($ligne);
        }

        $facture->lignes()->delete();
        $this->paiementScolariteService->supprimerEntreeComptableFacture($facture);
    }

    private function preparerDonneesEmission(
        array $data,
        Eleve $eleve,
        AnneeScolaire $anneeScolaire,
        ?Facture $factureEdition = null
    ): array {
        if ($eleve->exempte_frais) {
            throw new \RuntimeException('Cet élève est exempté de frais de scolarité.');
        }

        $mode = $data['mode'] ?? 'mois';
        $tarif = $this->getTarifClasse($eleve, $anneeScolaire);
        $reference = $data['reference_paiement'] ?? null;
        $observations = $data['observations'] ?? null;

        if ($mode === 'montant') {
            $montantVerse = round((float) ($data['montant_verse'] ?? 0), 2);
            $typeFrais = $data['type_frais_cible'] ?? 'scolarite';
            $montantRemise = $this->calculerMontantRemise($montantVerse, $data['remise_type'], (float) ($data['remise_valeur'] ?? 0));
            $montantNet = max(0, round($montantVerse - $montantRemise, 2));

            if ($montantNet <= 0) {
                throw new \RuntimeException('Le montant versé après remise doit être supérieur à zéro.');
            }

            $lignesSelection = $this->repartirMontantSurMois($eleve, $typeFrais, $montantNet, $anneeScolaire);

            $totaux = [
                'sous_total' => $montantVerse,
                'montant_remise' => $montantRemise,
                'total' => $montantNet,
                'lignes' => array_map(fn ($l) => array_merge($l, [
                    'montant_brut' => $l['montant'],
                    'montant_remise' => 0,
                    'montant_net' => $l['montant'],
                ]), $lignesSelection),
            ];

            $suffixe = 'Encaissement ' . number_format($montantVerse, 0, ',', ' ') . ' GNF — répartition automatique';
            $observations = $observations ? $observations . ' | ' . $suffixe : $suffixe;
        } else {
            $lignesDisponibles = collect(
                $factureEdition
                    ? $this->getLignesPourEditionFacture($factureEdition)
                    : $this->getLignesDisponibles($eleve, $anneeScolaire)
            )->keyBy('id');

            $lignesSelection = [];

            foreach ($data['lignes'] ?? [] as $ligneId) {
                $ligne = $lignesDisponibles->get($ligneId);
                if (!$ligne) {
                    throw new \RuntimeException('Une ligne sélectionnée n\'est plus disponible.');
                }

                $lignesSelection[] = [
                    'id' => $ligne['id'],
                    'source' => $ligne['source'],
                    'type_frais' => $ligne['type_frais'],
                    'mois' => $ligne['mois'],
                    'libelle' => $ligne['libelle'],
                    'montant' => (float) $ligne['montant'],
                    'montant_du_mois' => (float) ($ligne['montant_du_mois'] ?? $ligne['montant']),
                    'tranche_id' => $ligne['tranche_id'],
                    'frais_id' => $ligne['frais_id'],
                ];
            }

            if (empty($lignesSelection)) {
                throw new \RuntimeException('Sélectionnez au moins une ligne à facturer.');
            }

            $lignesSelection = collect($lignesSelection)->sortBy('mois')->values()->all();

            $montantVerse = round((float) ($data['montant_verse'] ?? 0), 2);
            if ($montantVerse <= 0) {
                throw new \RuntimeException('Le montant versé doit être supérieur à zéro.');
            }

            $totaux = $this->calculerTotauxAvecVersement(
                $lignesSelection,
                $data['remise_type'],
                (float) ($data['remise_valeur'] ?? 0),
                $montantVerse
            );

            if (count($lignesSelection) > 1) {
                $libellesMois = collect($lignesSelection)->pluck('libelle')->implode(', ');
                $suffixe = 'Paiement multi-mois : ' . $libellesMois;
                $observations = $observations ? $observations . ' | ' . $suffixe : $suffixe;
            }
        }

        return [
            'data' => $data,
            'totaux' => $totaux,
            'tarif' => $tarif,
            'reference' => $reference,
            'observations' => $observations,
        ];
    }

    private function enregistrerLignesEtPaiementsFacture(
        Facture $facture,
        Eleve $eleve,
        AnneeScolaire $anneeScolaire,
        ?TarifClasse $tarif,
        array $totaux,
        array $data,
        string $numeroFacture,
        ?string $observations
    ): void {
        $lignesTriees = collect($totaux['lignes'])->sortBy([
            ['mois', 'asc'],
            ['type_frais', 'asc'],
        ])->values()->all();

        foreach ($lignesTriees as $ligneCalculee) {
            $montantAPayerLigne = round((float) ($ligneCalculee['montant_net'] ?? 0), 2);
            if ($montantAPayerLigne <= 0 || !empty($ligneCalculee['non_paye'])) {
                continue;
            }

            $tranche = $this->resoudreTranche($eleve, $anneeScolaire, $tarif, $ligneCalculee);
            $tranche->refresh();

            $resteTranche = round((float) $tranche->montant_tranche - (float) $tranche->montant_paye, 2);
            $montantAPayer = $montantAPayerLigne;
            $remiseLigne = round((float) ($ligneCalculee['remise_ligne'] ?? 0), 2);
            $creditTranche = round($montantAPayer + $remiseLigne, 2);

            if ($creditTranche > $resteTranche + 0.01) {
                throw new \RuntimeException(
                    'Le montant pour « ' . ($ligneCalculee['libelle'] ?? '') . ' » dépasse le reste dû sur la tranche.'
                );
            }

            if ($montantAPayer <= 0 || $creditTranche <= 0) {
                throw new \RuntimeException(
                    'Le mois « ' . ($ligneCalculee['libelle'] ?? '') . ' » est déjà soldé. Rechargez la page et réessayez.'
                );
            }

            $paiement = $this->paiementScolariteService->enregistrerPaiementTranche(
                $tranche,
                $montantAPayer,
                $data['date_facture'],
                $data['mode_paiement'],
                $numeroFacture,
                $observations,
                (int) auth()->id(),
                false,
                $remiseLigne
            );

            FactureLigne::create([
                'facture_id' => $facture->id,
                'type_frais' => $ligneCalculee['type_frais'],
                'mois' => $ligneCalculee['mois'],
                'libelle' => $ligneCalculee['libelle'],
                'montant_brut' => $creditTranche,
                'montant_remise' => $remiseLigne,
                'montant_net' => $montantAPayer,
                'tranche_paiement_id' => $tranche->id,
                'frais_scolarite_id' => $tranche->frais_scolarite_id,
                'paiement_id' => $paiement->id,
            ]);
        }
    }

    private function ligneIdFromFactureLigne(FactureLigne $ligne): string
    {
        $mois = Carbon::parse($ligne->mois);

        if ($ligne->tranche_paiement_id) {
            return 'tranche:' . $ligne->tranche_paiement_id;
        }

        return 'tarif:' . $ligne->type_frais . ':' . $mois->format('Y-m');
    }

    /**
     * Montant dû sur un mois couvert par la facture = reste actuel + crédit déjà appliqué par cette facture.
     */
    private function montantLigneFacturePourEdition(FactureLigne $ligne, ?array $ligneDisponible = null): float
    {
        $creditFacture = round((float) $ligne->montant_brut, 2);

        if ($ligneDisponible !== null) {
            return round((float) $ligneDisponible['montant'] + $creditFacture, 2);
        }

        $ligne->loadMissing('tranchePaiement');
        $tranche = $ligne->tranchePaiement;

        if ($tranche) {
            $reste = max(0, round((float) $tranche->montant_tranche - (float) $tranche->montant_paye, 2));

            return round($reste + $creditFacture, 2);
        }

        return $creditFacture;
    }

    private function resoudreTranche(
        Eleve $eleve,
        AnneeScolaire $anneeScolaire,
        ?TarifClasse $tarif,
        array $ligne
    ): TranchePaiement {
        if (!empty($ligne['tranche_id'])) {
            $tranche = TranchePaiement::with('fraisScolarite')->findOrFail($ligne['tranche_id']);
            if ($tranche->statut === 'paye') {
                throw new \RuntimeException("La tranche « {$ligne['libelle']} » est déjà payée.");
            }

            return $tranche;
        }

        if (!$tarif) {
            throw new \RuntimeException('Aucun tarif de classe configuré pour créer les frais manquants.');
        }

        $mois = Carbon::parse($ligne['mois'])->startOfMonth();
        $frais = $this->assurerFrais($eleve, $anneeScolaire, $tarif, $ligne['type_frais']);
        $montantMensuel = (float) ($ligne['montant_du_mois'] ?? $ligne['montant_brut'] ?? $ligne['montant'] ?? 0);

        return $this->assurerTrancheMois($frais, $mois, $montantMensuel, $anneeScolaire);
    }

    private function assurerFrais(
        Eleve $eleve,
        AnneeScolaire $anneeScolaire,
        TarifClasse $tarif,
        string $typeFrais
    ): FraisScolarite {
        $frais = FraisScolarite::where('eleve_id', $eleve->id)
            ->where('type_frais', $typeFrais)
            ->where('statut', '!=', 'annule')
            ->first();

        if ($frais) {
            if ($frais->paiement_par_tranches && $frais->tranchesPaiement()->count() === 0) {
                $frais->creerTranchesPaiement();
                $frais->refresh();
            }

            return $frais;
        }

        $montantMensuel = $this->montantMensuelTarif($tarif, $typeFrais);
        $classeNom = $eleve->classe?->nom ?? 'Classe';
        $dateDebut = $this->dateDebutTranches(null, $anneeScolaire);

        $frais = FraisScolarite::create([
            'eleve_id' => $eleve->id,
            'libelle' => (self::LABELS_TYPE[$typeFrais] ?? ucfirst($typeFrais)) . ' - ' . $classeNom . ' - ' . $anneeScolaire->nom,
            'montant' => $montantMensuel * $tarif->nombre_tranches,
            'date_echeance' => $dateDebut->copy()->addMonths($tarif->nombre_tranches - 1),
            'type_frais' => $typeFrais,
            'statut' => 'en_attente',
            'paiement_par_tranches' => true,
            'nombre_tranches' => $tarif->nombre_tranches,
            'montant_tranche' => $montantMensuel,
            'periode_tranche' => $tarif->periode_tranche ?? 'mensuel',
            'date_debut_tranches' => $dateDebut->format('Y-m-d'),
            'actif' => true,
        ]);

        $frais->creerTranchesPaiement();

        return $frais->fresh(['tranchesPaiement']);
    }

    private function assurerTrancheMois(
        FraisScolarite $frais,
        Carbon $mois,
        float $montantAttendu,
        AnneeScolaire $anneeScolaire
    ): TranchePaiement {
        $this->realignerTranchesSiNecessaire($frais, $anneeScolaire);
        $frais->loadMissing('tranchesPaiement');
        $mois = $mois->copy()->startOfMonth();
        $debutPeriode = Carbon::parse($anneeScolaire->date_debut)->startOfMonth();

        $tranche = $frais->tranchesPaiement
            ->filter(fn (TranchePaiement $t) => $t->numero_tranche <= (int) $frais->nombre_tranches)
            ->first(function (TranchePaiement $t) use ($mois) {
                return Carbon::parse($t->date_echeance)->format('Y-m') === $mois->format('Y-m');
            });

        if ($tranche) {
            if ($tranche->statut === 'paye') {
                throw new \RuntimeException('La tranche du mois sélectionné est déjà payée.');
            }

            return $tranche;
        }

        if ($mois->lt($debutPeriode)) {
            throw new \RuntimeException('Le mois sélectionné est antérieur au début de l\'année scolaire.');
        }

        $numero = $debutPeriode->diffInMonths($mois) + 1;
        if ($numero > (int) $frais->nombre_tranches) {
            throw new \RuntimeException('Le mois sélectionné est hors de la période de facturation (' . (int) $frais->nombre_tranches . ' mois).');
        }

        $tranche = $frais->tranchesPaiement->firstWhere('numero_tranche', $numero);
        if ($tranche) {
            if ($tranche->statut === 'paye') {
                throw new \RuntimeException('La tranche du mois sélectionné est déjà payée.');
            }

            return $tranche;
        }

        if ($frais->tranchesPaiement->where('numero_tranche', '<=', (int) $frais->nombre_tranches)->count() >= (int) $frais->nombre_tranches) {
            throw new \RuntimeException('Toutes les tranches mensuelles sont déjà créées pour ce frais.');
        }

        return TranchePaiement::create([
            'frais_scolarite_id' => $frais->id,
            'numero_tranche' => $numero,
            'montant_tranche' => $montantAttendu,
            'date_echeance' => $this->dateEcheanceTranche($debutPeriode, $frais->periode_tranche ?? 'mensuel', $numero),
            'statut' => 'en_attente',
            'montant_paye' => 0,
        ]);
    }

    /**
     * Réaligne les tranches sur le début de l'année scolaire si elles ont été créées
     * au mauvais mois (ex. date d'inscription) et qu'aucun paiement n'a été enregistré.
     */
    private function realignerTranchesSiNecessaire(FraisScolarite $frais, AnneeScolaire $anneeScolaire): void
    {
        if (!$frais->paiement_par_tranches || !$frais->nombre_tranches) {
            return;
        }

        $debutAnnee = Carbon::parse($anneeScolaire->date_debut)->startOfMonth();
        $debutFrais = Carbon::parse($frais->date_debut_tranches ?? $anneeScolaire->date_debut)->startOfMonth();

        if ($debutFrais->eq($debutAnnee)) {
            return;
        }

        $aDesPaiements = $frais->tranchesPaiement()->where('montant_paye', '>', 0)->exists()
            || $frais->paiements()->exists();

        if ($aDesPaiements) {
            return;
        }

        $frais->tranchesPaiement()->delete();
        $frais->update(['date_debut_tranches' => $debutAnnee->format('Y-m-d')]);
        $frais->creerTranchesPaiement();
        $frais->unsetRelation('tranchesPaiement');
    }

    private function dateEcheanceTranche(Carbon $dateDebut, string $periode, int $numeroTranche): string
    {
        $date = $dateDebut->copy();

        return match ($periode) {
            'trimestriel' => $date->addMonths(($numeroTranche - 1) * 3)->toDateString(),
            'semestriel' => $date->addMonths(($numeroTranche - 1) * 6)->toDateString(),
            'annuel' => $date->addYears($numeroTranche - 1)->toDateString(),
            default => $date->addMonths($numeroTranche - 1)->toDateString(),
        };
    }

    private function getTarifClasse(Eleve $eleve, AnneeScolaire $anneeScolaire): ?TarifClasse
    {
        if (!$eleve->classe_id) {
            return null;
        }

        return TarifClasse::where('classe_id', $eleve->classe_id)
            ->where('annee_scolaire', $anneeScolaire->nom)
            ->where('actif', true)
            ->first()
            ?? TarifClasse::where('classe_id', $eleve->classe_id)
                ->where('actif', true)
                ->orderByDesc('id')
                ->first();
    }

    /** Date de début de la période de facturation = début de l'année scolaire. */
    private function dateDebutTranches(?FraisScolarite $frais, AnneeScolaire $anneeScolaire): Carbon
    {
        return Carbon::parse($anneeScolaire->date_debut)->startOfMonth();
    }

    /** @return Collection<int, Carbon> */
    private function moisPeriodeTranches(Carbon $dateDebut, int $nombreTranches): Collection
    {
        $debut = $dateDebut->copy()->startOfMonth();
        $mois = collect();

        for ($i = 0; $i < $nombreTranches; $i++) {
            $mois->push($debut->copy()->addMonths($i));
        }

        return $mois;
    }

    private function montantMensuelTarif(TarifClasse $tarif, string $type): float
    {
        return match ($type) {
            'scolarite' => (float) $tarif->frais_scolarite_mensuel,
            'cantine' => (float) $tarif->frais_cantine_mensuel,
            'transport' => (float) $tarif->frais_transport_mensuel,
            default => 0,
        };
    }

    private function calculerMontantRemise(float $sousTotal, string $remiseType, float $remiseValeur): float
    {
        if ($sousTotal <= 0 || $remiseValeur <= 0) {
            return 0;
        }

        $montant = $remiseType === 'pourcentage'
            ? round($sousTotal * min($remiseValeur, 100) / 100, 2)
            : round(min($remiseValeur, $sousTotal), 2);

        return min($montant, $sousTotal);
    }

    private function libelleLigne(string $typeFrais, Carbon $mois): string
    {
        $label = self::LABELS_TYPE[$typeFrais] ?? ucfirst($typeFrais);
        $nomsMois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return $label . ' — ' . ($nomsMois[(int) $mois->format('n')] ?? $mois->format('m')) . ' ' . $mois->format('Y');
    }

    private function ligneKey(string $typeFrais, Carbon $mois): string
    {
        return $typeFrais . ':' . $mois->format('Y-m');
    }

    private function formatLigne(array $ligne): array
    {
        return $ligne;
    }
}
