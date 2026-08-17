<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TarifClasse;
use App\Models\TranchePaiement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacturationService
{
    private const TYPES_MENSUELS = ['scolarite', 'cantine', 'transport'];

    /** Mois facturables de l'année scolaire : octobre → juin (9 mois). */
    private const MOIS_FACTURATION_ANNEE = [10, 11, 12, 1, 2, 3, 4, 5, 6];

    private const LABELS_TYPE = [
        'scolarite' => 'Scolarité',
        'cantine' => 'Cantine',
        'transport' => 'Transport',
        'inscription' => 'Inscription',
        'reinscription' => 'Réinscription',
    ];

    private const TYPES_ENTREE = ['inscription', 'reinscription'];

    public function __construct(
        private PaiementScolariteService $paiementScolariteService
    ) {}

    /**
     * Génère tous les frais scolaires d'un élève à partir du tarif de sa classe.
     */
    public function genererFraisInscriptionEleve(
        Eleve $eleve,
        bool $gratuitInscription = false,
        bool $gratuitReinscription = false
    ): void {
        if ($eleve->exempte_frais) {
            return;
        }

        $eleve->loadMissing(['classe', 'anneeScolaire']);

        $anneeScolaire = $eleve->anneeScolaire ?? AnneeScolaire::anneeActive();
        if (!$anneeScolaire || !$eleve->classe_id) {
            return;
        }

        $tarif = $this->getTarifClasse($eleve, $anneeScolaire);
        if (!$tarif) {
            return;
        }

        DB::transaction(function () use ($eleve, $anneeScolaire, $tarif, $gratuitInscription, $gratuitReinscription) {
            $this->creerFraisEntreeInscription($eleve, $tarif, $gratuitInscription, $gratuitReinscription);

            foreach (self::TYPES_MENSUELS as $type) {
                if ($this->montantMensuelTarif($tarif, $type) > 0) {
                    $this->assurerFrais($eleve, $anneeScolaire, $tarif, $type);
                }
            }

            $this->creerFraisUnique($eleve, $anneeScolaire, 'Frais d\'uniforme', (float) $tarif->frais_uniforme);
            $this->creerFraisUnique($eleve, $anneeScolaire, 'Frais de livres', (float) $tarif->frais_livres);
            $this->creerFraisUnique($eleve, $anneeScolaire, 'Autres frais', (float) $tarif->frais_autres);
        });
    }

    public function getLignesDisponibles(Eleve $eleve, ?AnneeScolaire $anneeScolaire = null): array
    {
        $anneeScolaire = $anneeScolaire ?? AnneeScolaire::anneeActive();
        if (!$anneeScolaire) {
            return [];
        }

        $eleve->loadMissing(['classe']);
        $tarif = $this->getTarifClasse($eleve, $anneeScolaire);

        if ($tarif) {
            $this->assurerFraisEleve($eleve, $anneeScolaire, $tarif);
        }

        $lignes = $this->getLignesFraisUnitaires($eleve);
        if ($tarif) {
            $lignes = $lignes->merge($this->getLignesEntreeDepuisTarif($eleve, $tarif, $lignes));
        }

        if (!$tarif) {
            return $this->trierLignesDisponibles($lignes);
        }

        $moisFacturation = $this->moisAnneeScolaireFacturation($anneeScolaire);

        $fraisList = FraisScolarite::where('eleve_id', $eleve->id)
            ->whereIn('type_frais', self::TYPES_MENSUELS)
            ->where('statut', '!=', 'annule')
            ->with(['tranchesPaiement' => fn ($q) => $q->orderBy('numero_tranche')])
            ->get();

        foreach ($fraisList as $frais) {
            $this->realignerTranchesSiNecessaire($frais, $anneeScolaire);
        }
        $fraisList->load(['tranchesPaiement' => fn ($q) => $q->orderBy('numero_tranche')]);

        foreach (self::TYPES_MENSUELS as $type) {
            $montantMensuel = $this->montantMensuelTarif($tarif, $type);
            if ($montantMensuel <= 0) {
                continue;
            }

            $frais = $fraisList->firstWhere('type_frais', $type);

            foreach ($moisFacturation as $mois) {
                $tranche = $frais?->tranchesPaiement->first(
                    fn (TranchePaiement $t) => Carbon::parse($t->date_echeance)->format('Y-m') === $mois->format('Y-m')
                );

                if ($tranche) {
                    $tranche = $this->reconcilierTrancheSiSoldée($tranche);
                    $montantTranche = (float) $tranche->montant_tranche;
                    $reste = $this->resteEffectifTranche($tranche);

                    if ($tranche->statut === 'paye' || $reste <= 0.01) {
                        continue;
                    }

                    $moisRef = Carbon::parse($tranche->date_echeance)->startOfMonth();
                    $libelle = $this->libelleLigne($type, $moisRef);

                    $lignes->push($this->formatLigne([
                        'id' => 'tranche:' . $tranche->id,
                        'source' => 'tranche',
                        'type_frais' => $type,
                        'mois' => $moisRef->format('Y-m-d'),
                        'libelle' => $libelle,
                        'libelle_mois' => self::formatLibelleMois($moisRef),
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
                        'libelle_mois' => self::formatLibelleMois($mois),
                        'montant' => round($montantMensuel, 2),
                        'montant_du_mois' => round($montantMensuel, 2),
                        'partiel' => false,
                        'tranche_id' => null,
                        'frais_id' => $frais?->id,
                    ]));
                }
            }
        }

        return $this->trierLignesDisponibles($lignes);
    }

    public function aFraisImpayes(Eleve $eleve, ?AnneeScolaire $anneeScolaire = null): bool
    {
        if ($eleve->exempte_frais) {
            return false;
        }

        if (count($this->getLignesDisponibles($eleve, $anneeScolaire)) > 0) {
            return true;
        }

        return FraisScolarite::where('eleve_id', $eleve->id)
            ->whereIn('statut', ['en_attente', 'en_retard'])
            ->exists();
    }

    /**
     * @param  array<int, array{id: string, montant: float}>  $lignesSelection
     * @return array{sous_total: float, montant_remise: float, total: float, lignes: array<int, array<string, mixed>>}
     */
    public function calculerTotaux(array $lignesSelection, string $remiseType, float $remiseValeur): array
    {
        $lignesTriees = $this->ordonnerLignesPourPaiement($lignesSelection);
        $sousTotal = round(collect($lignesTriees)->sum('montant'), 2);
        $montantRemiseDemandee = $this->calculerMontantRemise($sousTotal, $remiseType, $remiseValeur);
        $sousTotalRemisable = $this->sousTotalRemisable($lignesTriees);
        $montantRemise = round(min($montantRemiseDemandee, $sousTotalRemisable), 2);
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
     * Chaque ligne conserve son montant brut (inscription 30 000, scolarité 120 000, etc.).
     * La remise globale ne réduit pas le montant dû affiché par ligne.
     *
     * @param  array<int, array<string, mixed>>  $lignesSelection
     * @return array<int, array<string, mixed>>
     */
    private function lignesAvecMontantDu(array $lignesSelection, float $montantRemise): array
    {
        unset($montantRemise);

        $lignesTriees = $this->ordonnerLignesPourPaiement($lignesSelection);
        $result = [];

        foreach ($lignesTriees as $ligne) {
            $brut = round((float) $ligne['montant'], 2);

            $result[] = array_merge($ligne, [
                'montant_brut' => $brut,
                'montant_du' => $brut,
                'remise_ligne' => 0,
                'montant_remise' => 0,
                'montant_net' => $brut,
            ]);
        }

        return $result;
    }

    /**
     * Montant sur lequel une remise globale peut être appliquée (hors inscription / réinscription).
     *
     * @param  array<int, array<string, mixed>>  $lignes
     */
    private function sousTotalRemisable(array $lignes): float
    {
        return round(
            collect($lignes)
                ->filter(fn (array $ligne) => $this->estLigneRemisable($ligne))
                ->sum(fn (array $ligne) => (float) $ligne['montant']),
            2
        );
    }

    private function estLigneRemisable(array $ligne): bool
    {
        return !in_array($ligne['type_frais'] ?? '', self::TYPES_ENTREE, true);
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
        $lignesTriees = $this->ordonnerLignesPourPaiement($lignesSelection);
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

        $resteAPayer = max(0, round($totalDu - $montantVerse, 2));
        $reste = $montantVerse;
        $lignesPayees = [];

        foreach ($totaux['lignes'] as $ligne) {
            if ($reste <= 0) {
                break;
            }

            $brutMois = round((float) ($ligne['montant_brut'] ?? $ligne['montant'] ?? 0), 2);
            if ($brutMois <= 0) {
                continue;
            }

            $paye = round(min($brutMois, $reste), 2);

            if ($paye <= 0) {
                continue;
            }

            $partiel = $paye + 0.00001 < $brutMois;
            $libelle = $this->nettoyerLibelleAffichage($ligne['libelle'] ?? '');

            $lignesPayees[] = array_merge($ligne, [
                'libelle' => $libelle,
                'montant_brut' => $brutMois,
                'montant_du' => $brutMois,
                'montant_remise' => 0,
                'montant_net' => $paye,
                'remise_ligne' => 0,
                'reste' => 0,
                'partiel' => $partiel,
            ]);

            $reste = round($reste - $paye, 2);
        }

        $this->affecterRemiseSurDerniereLignePayee($lignesPayees, $totaux['montant_remise'], $resteAPayer);

        // Mois non touchés par le paiement mais encore dus
        $idsPayes = collect($lignesPayees)->pluck('id')->all();
        $aUnPaiementPartiel = collect($lignesPayees)->contains(fn (array $l) => !empty($l['partiel']));

        foreach ($totaux['lignes'] as $ligne) {
            if (in_array($ligne['id'] ?? null, $idsPayes, true)) {
                continue;
            }

            $brut = round((float) ($ligne['montant_brut'] ?? $ligne['montant'] ?? 0), 2);
            if ($brut <= 0) {
                continue;
            }

            $libelle = $this->nettoyerLibelleAffichage($ligne['libelle'] ?? '');
            $lignesPayees[] = array_merge($ligne, [
                'libelle' => $libelle,
                'montant_brut' => $brut,
                'montant_du' => $brut,
                'montant_remise' => 0,
                'montant_net' => 0,
                'remise_ligne' => 0,
                'reste' => $aUnPaiementPartiel ? 0 : $brut,
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
            'reste_a_payer' => $resteAPayer,
            'lignes' => $lignesPayees,
        ];
    }

    /**
     * La remise globale est imputée sur la dernière ligne de scolarité encaissée ;
     * le reste affiché sur une ligne partielle correspond au solde global de la facture.
     *
     * @param  array<int, array<string, mixed>>  $lignesPayees
     */
    private function affecterRemiseSurDerniereLignePayee(array &$lignesPayees, float $montantRemise, float $resteAPayer): void
    {
        $montantRemise = round($montantRemise, 2);
        if ($montantRemise <= 0 || empty($lignesPayees)) {
            if ($resteAPayer > 0) {
                for ($i = count($lignesPayees) - 1; $i >= 0; $i--) {
                    if (!empty($lignesPayees[$i]['partiel'])) {
                        $lignesPayees[$i]['reste'] = $resteAPayer;
                        break;
                    }
                }
            }

            return;
        }

        for ($i = count($lignesPayees) - 1; $i >= 0; $i--) {
            if (empty($lignesPayees[$i]['non_paye']) && $this->estLigneRemisable($lignesPayees[$i])) {
                $lignesPayees[$i]['remise_ligne'] = $montantRemise;
                $lignesPayees[$i]['montant_remise'] = $montantRemise;

                if (!empty($lignesPayees[$i]['partiel']) && $resteAPayer > 0) {
                    $lignesPayees[$i]['reste'] = $resteAPayer;
                }

                break;
            }
        }
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
            $libelle = $this->nettoyerLibelleAffichage($ligne['libelle'] ?? '');

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
                'statut' => $this->statutDepuisTotaux($preparation['totaux']),
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
            $libelle = $this->nettoyerLibelleAffichage($ligne->libelle) ?: $ligne->libelle;
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
                'source' => $ligne->tranche_paiement_id
                    ? 'tranche'
                    : ($ligne->frais_scolarite_id ? 'frais' : 'tarif'),
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

    public function modifierFacture(Facture $facture, array $data): Facture
    {
        if (!$facture->estModifiable()) {
            throw new \RuntimeException('Seules les factures payées ou en cours peuvent être modifiées.');
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
                'statut' => $this->statutDepuisTotaux($preparation['totaux']),
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

    /**
     * Encaisse le solde restant d'une facture en cours sans repasser par l'écran de modification.
     */
    public function payerResteFacture(Facture $facture, array $data): Facture
    {
        if ($facture->statut !== 'en_cours') {
            throw new \RuntimeException('Seules les factures en cours peuvent recevoir un paiement du solde.');
        }

        if ($facture->estFactureComplement()) {
            throw new \RuntimeException('Impossible de payer un solde sur une facture complémentaire.');
        }

        $reste = $facture->resteAPayer();
        if ($reste <= 0.01) {
            throw new \RuntimeException('Cette facture est déjà entièrement payée.');
        }

        $eleve = Eleve::with('classe')->findOrFail($facture->eleve_id);
        $anneeScolaire = $facture->anneeScolaire ?? AnneeScolaire::find($facture->annee_scolaire_id);

        if (!$anneeScolaire) {
            throw new \RuntimeException('Année scolaire introuvable pour cette facture.');
        }

        if ($eleve->exempte_frais) {
            throw new \RuntimeException('Cet élève est exempté de frais de scolarité.');
        }

        $tarif = $this->getTarifClasse($eleve, $anneeScolaire);
        if ($tarif) {
            $this->assurerFraisEleve($eleve, $anneeScolaire, $tarif);
        }

        $typeFrais = $this->detecterTypeFraisPourSolde($facture, $eleve, $anneeScolaire);
        $datePaiement = $data['date_paiement'] ?? now()->toDateString();

        $observationsComplement = 'Solde de la facture ' . $facture->numero_facture;
        if (!empty($data['observations'])) {
            $observationsComplement .= ' | ' . trim($data['observations']);
        }

        return DB::transaction(function () use (
            $facture,
            $data,
            $reste,
            $eleve,
            $anneeScolaire,
            $tarif,
            $typeFrais,
            $datePaiement,
            $observationsComplement
        ) {
            $lignesRepartition = $this->repartirMontantSurMois($eleve, $typeFrais, $reste, $anneeScolaire);

            $lignesCalculees = array_map(fn (array $ligne) => [
                'id' => $ligne['id'],
                'source' => $ligne['source'],
                'type_frais' => $ligne['type_frais'],
                'mois' => $ligne['mois'],
                'libelle' => 'Reste à payer',
                'montant_brut' => (float) $ligne['montant'],
                'montant_du_mois' => (float) ($ligne['montant_du_mois'] ?? $ligne['montant']),
                'montant_remise' => 0,
                'montant_net' => (float) $ligne['montant'],
                'remise_ligne' => 0,
                'tranche_id' => $ligne['tranche_id'] ?? null,
                'frais_id' => $ligne['frais_id'] ?? null,
                'partiel' => (bool) ($ligne['partiel'] ?? false),
            ], $lignesRepartition);

            $montantReste = round($reste, 2);

            $factureComplement = Facture::create([
                'eleve_id' => $facture->eleve_id,
                'annee_scolaire_id' => $facture->annee_scolaire_id,
                'facture_origine_id' => $facture->id,
                'date_facture' => $datePaiement,
                'date_echeance' => null,
                'sous_total' => $montantReste,
                'remise_type' => 'montant',
                'remise_valeur' => 0,
                'montant_remise' => 0,
                'total' => $montantReste,
                'mode_paiement' => $data['mode_paiement'],
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'observations' => $observationsComplement,
                'statut' => 'payee',
                'genere_par' => auth()->id(),
            ]);

            $dataEmission = [
                'date_facture' => $datePaiement,
                'mode_paiement' => $data['mode_paiement'],
            ];

            $this->enregistrerLignesEtPaiementsFacture(
                $factureComplement,
                $eleve,
                $anneeScolaire,
                $tarif,
                [
                    'sous_total' => $montantReste,
                    'montant_remise' => 0,
                    'total' => $montantReste,
                    'lignes' => $lignesCalculees,
                ],
                $dataEmission,
                $factureComplement->numero_facture,
                $observationsComplement
            );

            $this->paiementScolariteService->creerEntreeComptableFacture($factureComplement->fresh(['lignes']));

            // Facture initiale : seul le statut passe à payée (montant et entrée comptable inchangés)
            $facture->update(['statut' => 'payee']);

            return $factureComplement->fresh(['lignes', 'eleve.utilisateur', 'eleve.classe', 'generePar', 'factureOrigine']);
        });
    }

    /**
     * Détermine sur quel type de frais répartir le solde (mois impayés).
     */
    private function detecterTypeFraisPourSolde(Facture $facture, Eleve $eleve, AnneeScolaire $anneeScolaire): string
    {
        $reste = $facture->resteAPayer();
        $disponibles = collect($this->getLignesDisponibles($eleve, $anneeScolaire))
            ->filter(fn (array $ligne) => (float) ($ligne['montant'] ?? 0) > 0.01);

        foreach (['scolarite', 'cantine', 'transport'] as $type) {
            $somme = $disponibles->where('type_frais', $type)->sum(fn (array $ligne) => (float) $ligne['montant']);
            if ($somme + 0.01 >= $reste) {
                return $type;
            }
        }

        $premierType = $disponibles->pluck('type_frais')->first();
        if ($premierType && in_array($premierType, self::TYPES_MENSUELS, true)) {
            return $premierType;
        }

        return 'scolarite';
    }

    public function supprimerFacture(Facture $facture): void
    {
        if ($facture->statut === 'annulee') {
            throw new \RuntimeException('Cette facture est déjà annulée.');
        }

        DB::transaction(function () use ($facture) {
            $this->annulerEffetsFacture($facture);
            $facture->delete();
        });
    }

    /**
     * Annule une facture : retire paiements, tranches et entrée comptable, conserve la facture en historique.
     */
    public function annulerFacture(Facture $facture): void
    {
        if ($facture->statut === 'annulee') {
            throw new \RuntimeException('Cette facture est déjà annulée.');
        }

        DB::transaction(function () use ($facture) {
            $this->annulerEffetsFacture($facture);
            $facture->update(['statut' => 'annulee']);
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
        if ($tarif) {
            $this->assurerFraisEleve($eleve, $anneeScolaire, $tarif);
        }
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

            $lignesSelection = $this->ordonnerLignesPourPaiement($lignesSelection);

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
        $lignesTriees = $this->ordonnerLignesPourPaiement($totaux['lignes']);

        foreach ($lignesTriees as $ligneCalculee) {
            $montantAPayerLigne = round((float) ($ligneCalculee['montant_net'] ?? 0), 2);
            if ($montantAPayerLigne <= 0 || !empty($ligneCalculee['non_paye'])) {
                continue;
            }

            if ($this->estLigneFraisUnitaire($ligneCalculee)) {
                $frais = $this->resoudreFraisUnitaire($eleve, $tarif, $ligneCalculee);
                $remiseLigne = round((float) ($ligneCalculee['remise_ligne'] ?? 0), 2);
                $montantBrut = round((float) ($ligneCalculee['montant_brut'] ?? $montantAPayerLigne + $remiseLigne), 2);
                $creditFrais = round($montantAPayerLigne + $remiseLigne, 2);
                $resteFrais = round((float) $frais->montant_restant, 2);

                if ($creditFrais - $resteFrais > 0.01) {
                    throw new \RuntimeException(
                        'Le montant pour « ' . ($ligneCalculee['libelle'] ?? '') . ' » dépasse le reste dû.'
                    );
                }

                if ($remiseLigne > 0) {
                    $frais->update([
                        'montant' => max(0, round((float) $frais->montant - $remiseLigne, 2)),
                    ]);
                    $frais->refresh();
                    $resteFrais = round((float) $frais->montant_restant, 2);
                }

                if ($montantAPayerLigne - $resteFrais > 0.01) {
                    throw new \RuntimeException(
                        'Le montant pour « ' . ($ligneCalculee['libelle'] ?? '') . ' » dépasse le reste dû.'
                    );
                }

                $paiement = Paiement::create([
                    'frais_scolarite_id' => $frais->id,
                    'montant_paye' => $montantAPayerLigne,
                    'date_paiement' => $data['date_facture'],
                    'mode_paiement' => $data['mode_paiement'],
                    'reference_paiement' => $numeroFacture,
                    'observations' => $observations,
                    'encaisse_par' => (int) auth()->id(),
                ]);

                $frais->refresh();
                if ((float) $frais->montant_restant <= 0.01) {
                    $frais->update(['statut' => 'paye']);
                }

                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'type_frais' => $ligneCalculee['type_frais'],
                    'mois' => $ligneCalculee['mois'],
                    'libelle' => $ligneCalculee['libelle'],
                    'montant_brut' => $montantBrut,
                    'montant_remise' => 0,
                    'montant_net' => $montantAPayerLigne,
                    'tranche_paiement_id' => null,
                    'frais_scolarite_id' => $frais->id,
                    'paiement_id' => $paiement->id,
                ]);

                continue;
            }

            $tranche = $this->resoudreTranche($eleve, $anneeScolaire, $tarif, $ligneCalculee);
            $tranche->refresh();

            $resteTranche = $this->resteEffectifTranche($tranche);
            $montantAPayer = $montantAPayerLigne;
            $remiseLigne = round((float) ($ligneCalculee['remise_ligne'] ?? 0), 2);
            // Ne pas confondre paiement partiel et remise (ex. solde restant sur un mois)
            if ($remiseLigne <= 0 && empty($ligneCalculee['partiel'])) {
                $brutLigne = round((float) ($ligneCalculee['montant_brut'] ?? 0), 2);
                $netLigne = round((float) ($ligneCalculee['montant_net'] ?? 0), 2);
                $remiseLigne = max(0, round($brutLigne - $netLigne, 2));
            }
            $creditTranche = round($montantAPayer + $remiseLigne, 2);
            $brutMois = round((float) (
                $ligneCalculee['montant_brut']
                ?? $ligneCalculee['montant_du_mois']
                ?? $tranche->montant_tranche
            ), 2);

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
                'montant_brut' => $brutMois,
                'montant_remise' => $remiseLigne,
                'montant_net' => $montantAPayer,
                'tranche_paiement_id' => $tranche->id,
                'frais_scolarite_id' => $tranche->frais_scolarite_id,
                'paiement_id' => $paiement->id,
            ]);

            $this->reconcilierTrancheSiSoldée($tranche->fresh());
        }
    }

    private function ligneIdFromFactureLigne(FactureLigne $ligne): string
    {
        if (!$ligne->tranche_paiement_id && $ligne->frais_scolarite_id) {
            return 'frais:' . $ligne->frais_scolarite_id;
        }

        $mois = Carbon::parse($ligne->mois);

        if ($ligne->tranche_paiement_id) {
            return 'tranche:' . $ligne->tranche_paiement_id;
        }

        if (in_array($ligne->type_frais, self::TYPES_ENTREE, true)) {
            return 'tarif:' . $ligne->type_frais;
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

        $ligne->loadMissing('fraisScolarite');
        $frais = $ligne->fraisScolarite;
        if ($frais) {
            $reste = max(0, round((float) $frais->montant_restant, 2));

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
        if (($ligne['source'] ?? '') === 'frais') {
            throw new \RuntimeException('Les frais unitaires ne passent pas par une tranche mensuelle.');
        }

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

    private function assurerFraisEleve(Eleve $eleve, AnneeScolaire $anneeScolaire, TarifClasse $tarif): void
    {
        $this->creerFraisEntreeInscription($eleve, $tarif, false, false);

        foreach (self::TYPES_MENSUELS as $type) {
            if ($this->montantMensuelTarif($tarif, $type) > 0) {
                $this->assurerFrais($eleve, $anneeScolaire, $tarif, $type);
            }
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getLignesEntreeDepuisTarif(Eleve $eleve, TarifClasse $tarif, Collection $lignesExistantes): Collection
    {
        $typesDeja = $lignesExistantes->pluck('type_frais')->all();
        $lignes = collect();

        if ($eleve->type_inscription === 'reinscription'
            && !in_array('reinscription', $typesDeja, true)
            && !$this->fraisExisteDeja($eleve->id, 'reinscription')) {
            $montant = $this->montantFraisReinscription($tarif);
            if ($montant > 0) {
                $lignes->push($this->formatLigneEntreeTarif('reinscription', $montant, 'Frais de réinscription'));
            }
        } elseif (in_array($eleve->type_inscription, ['nouvelle', 'transfert'], true)
            && !in_array('inscription', $typesDeja, true)
            && !$this->fraisExisteDeja($eleve->id, 'inscription')) {
            $montant = (float) $tarif->frais_inscription;
            if ($montant > 0) {
                $lignes->push($this->formatLigneEntreeTarif('inscription', $montant, 'Frais d\'inscription'));
            }
        }

        return $lignes;
    }

    private function formatLigneEntreeTarif(string $typeFrais, float $montant, string $libelle): array
    {
        return $this->formatLigne([
            'id' => 'tarif:' . $typeFrais,
            'source' => 'tarif',
            'type_frais' => $typeFrais,
            'mois' => now()->format('Y-m-d'),
            'libelle' => $libelle,
            'montant' => round($montant, 2),
            'montant_du_mois' => round($montant, 2),
            'partiel' => false,
            'tranche_id' => null,
            'frais_id' => null,
        ]);
    }

    private function estLigneFraisUnitaire(array $ligne): bool
    {
        if (($ligne['source'] ?? '') === 'frais') {
            return true;
        }

        return ($ligne['source'] ?? '') === 'tarif'
            && in_array($ligne['type_frais'] ?? '', self::TYPES_ENTREE, true);
    }

    private function resoudreFraisUnitaire(Eleve $eleve, ?TarifClasse $tarif, array $ligne): FraisScolarite
    {
        if (!empty($ligne['frais_id'])) {
            return FraisScolarite::findOrFail($ligne['frais_id']);
        }

        if (($ligne['source'] ?? '') === 'tarif' && $tarif) {
            $this->creerFraisEntreeInscription($eleve, $tarif, false, false);
        }

        return FraisScolarite::where('eleve_id', $eleve->id)
            ->where('type_frais', $ligne['type_frais'])
            ->whereIn('statut', ['en_attente', 'en_retard'])
            ->firstOrFail();
    }

    /**
     * Inscription / réinscription en premier, puis les mois de scolarité chronologiques.
     *
     * @param  array<int, array<string, mixed>>  $lignes
     * @return array<int, array<string, mixed>>
     */
    private function ordonnerLignesPourPaiement(array $lignes): array
    {
        return collect($lignes)
            ->sortBy([
                fn (array $ligne) => in_array($ligne['type_frais'] ?? '', self::TYPES_ENTREE, true) ? 0 : 1,
                ['mois', 'asc'],
                ['type_frais', 'asc'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $lignes
     * @return array<int, array<string, mixed>>
     */
    private function trierLignesDisponibles(Collection $lignes): array
    {
        return $this->ordonnerLignesPourPaiement($lignes->all());
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
        $moisFacturation = $this->moisAnneeScolaireFacturation($anneeScolaire);
        $dateDebut = $moisFacturation->first()->copy();
        $dateFin = $moisFacturation->last()->copy();
        $nombreMois = $this->nombreMoisFacturation();

        $frais = FraisScolarite::create([
            'eleve_id' => $eleve->id,
            'libelle' => (self::LABELS_TYPE[$typeFrais] ?? ucfirst($typeFrais)) . ' - ' . $classeNom . ' - ' . $anneeScolaire->nom,
            'montant' => $montantMensuel * $nombreMois,
            'date_echeance' => $dateFin,
            'type_frais' => $typeFrais,
            'statut' => 'en_attente',
            'paiement_par_tranches' => true,
            'nombre_tranches' => $nombreMois,
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

        $numero = $this->numeroTranchePourMoisAnneeScolaire($anneeScolaire, $mois);
        if ($numero === null) {
            throw new \RuntimeException('Le mois sélectionné est hors de la période de facturation (Octobre à Juin).');
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
            'date_echeance' => $mois->format('Y-m-d'),
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

        $debutAnnee = $this->dateDebutTranches(null, $anneeScolaire);
        $debutFrais = Carbon::parse($frais->date_debut_tranches ?? $debutAnnee)->startOfMonth();

        if ($debutFrais->eq($debutAnnee)) {
            return;
        }

        $aDesPaiements = $frais->tranchesPaiement()->where('montant_paye', '>', 0)->exists()
            || $frais->paiements()->exists();

        if ($aDesPaiements) {
            return;
        }

        $frais->tranchesPaiement()->delete();
        $frais->update([
            'date_debut_tranches' => $debutAnnee->format('Y-m-d'),
            'nombre_tranches' => $this->nombreMoisFacturation(),
        ]);
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

    /** Date de début de la période de facturation = octobre de l'année scolaire. */
    private function dateDebutTranches(?FraisScolarite $frais, AnneeScolaire $anneeScolaire): Carbon
    {
        return $this->moisAnneeScolaireFacturation($anneeScolaire)->first()->copy();
    }

    private function nombreMoisFacturation(): int
    {
        return count(self::MOIS_FACTURATION_ANNEE);
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function moisAnneeScolaireFacturation(AnneeScolaire $anneeScolaire): Collection
    {
        $anneeDebut = $this->anneeDebutFacturation($anneeScolaire);
        $anneeFin = $anneeDebut + 1;

        return collect(self::MOIS_FACTURATION_ANNEE)->map(
            fn (int $mois) => Carbon::create($mois >= 10 ? $anneeDebut : $anneeFin, $mois, 1)->startOfMonth()
        );
    }

    private function anneeDebutFacturation(AnneeScolaire $anneeScolaire): int
    {
        if (preg_match('#^(\d{4})\s*[-/]#', trim($anneeScolaire->nom), $matches)) {
            return (int) $matches[1];
        }

        $debut = Carbon::parse($anneeScolaire->date_debut);

        return $debut->month >= 10 ? $debut->year : $debut->year - 1;
    }

    private function numeroTranchePourMoisAnneeScolaire(AnneeScolaire $anneeScolaire, Carbon $mois): ?int
    {
        $cible = $mois->copy()->startOfMonth()->format('Y-m');

        foreach ($this->moisAnneeScolaireFacturation($anneeScolaire)->values() as $index => $periode) {
            if ($periode->format('Y-m') === $cible) {
                return $index + 1;
            }
        }

        return null;
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

    private function nettoyerLibelleAffichage(?string $libelle): string
    {
        $libelle = trim((string) $libelle);

        return trim((string) preg_replace('/\s*\((?:reste|partiel)[^)]*\)/u', '', $libelle));
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

    /**
     * Frais uniques impayés : inscription, réinscription, uniforme, etc.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function getLignesFraisUnitaires(Eleve $eleve): Collection
    {
        return FraisScolarite::where('eleve_id', $eleve->id)
            ->whereNotIn('type_frais', self::TYPES_MENSUELS)
            ->whereIn('statut', ['en_attente', 'en_retard'])
            ->get()
            ->map(function (FraisScolarite $frais) {
                $reste = round((float) $frais->montant_restant, 2);
                if ($reste <= 0) {
                    return null;
                }

                return $this->formatLigne([
                    'id' => 'frais:' . $frais->id,
                    'source' => 'frais',
                    'type_frais' => $frais->type_frais,
                    'mois' => ($frais->date_echeance ?? now())->format('Y-m-d'),
                    'libelle' => $frais->libelle,
                    'montant' => $reste,
                    'montant_du_mois' => $reste,
                    'partiel' => false,
                    'tranche_id' => null,
                    'frais_id' => $frais->id,
                ]);
            })
            ->filter()
            ->values();
    }

    private function creerFraisEntreeInscription(
        Eleve $eleve,
        TarifClasse $tarif,
        bool $gratuitInscription,
        bool $gratuitReinscription
    ): void {
        if ($eleve->type_inscription === 'reinscription') {
            if ($this->fraisExisteDeja($eleve->id, 'reinscription')) {
                return;
            }

            if ($gratuitReinscription) {
                FraisScolarite::create([
                    'eleve_id' => $eleve->id,
                    'libelle' => 'Frais de réinscription (GRATUIT)',
                    'montant' => 0,
                    'date_echeance' => now(),
                    'statut' => 'paye',
                    'type_frais' => 'reinscription',
                    'description' => 'Frais de réinscription GRATUIT pour l\'année scolaire',
                    'paiement_par_tranches' => false,
                    'actif' => true,
                ]);

                return;
            }

            $montant = $this->montantFraisReinscription($tarif);
            if ($montant <= 0) {
                return;
            }

            FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais de réinscription',
                'montant' => $montant,
                'date_echeance' => now()->addDays(30),
                'statut' => 'en_attente',
                'type_frais' => 'reinscription',
                'description' => 'Frais de réinscription pour l\'année scolaire',
                'paiement_par_tranches' => false,
                'actif' => true,
            ]);

            return;
        }

        if (!in_array($eleve->type_inscription, ['nouvelle', 'transfert'], true)) {
            return;
        }

        if ($this->fraisExisteDeja($eleve->id, 'inscription')) {
            return;
        }

        if ((float) $tarif->frais_inscription <= 0 && !$gratuitInscription) {
            return;
        }

        $montant = $gratuitInscription ? 0 : (float) $tarif->frais_inscription;
        $statut = $gratuitInscription ? 'paye' : 'en_attente';

        FraisScolarite::create([
            'eleve_id' => $eleve->id,
            'libelle' => 'Frais d\'inscription' . ($gratuitInscription ? ' (GRATUIT)' : ''),
            'montant' => $montant,
            'date_echeance' => $gratuitInscription ? now() : now()->addDays(30),
            'statut' => $statut,
            'type_frais' => 'inscription',
            'description' => $gratuitInscription
                ? 'Frais d\'inscription GRATUIT pour l\'année scolaire'
                : 'Frais d\'inscription pour l\'année scolaire',
            'paiement_par_tranches' => false,
            'actif' => true,
        ]);
    }

    private function creerFraisUnique(
        Eleve $eleve,
        AnneeScolaire $anneeScolaire,
        string $libelleBase,
        float $montant
    ): void {
        if ($montant <= 0) {
            return;
        }

        $libelle = $libelleBase . ' - ' . ($eleve->classe?->nom ?? 'Classe') . ' - ' . $anneeScolaire->nom;

        if (FraisScolarite::where('eleve_id', $eleve->id)
            ->where('libelle', $libelle)
            ->where('statut', '!=', 'annule')
            ->exists()) {
            return;
        }

        FraisScolarite::create([
            'eleve_id' => $eleve->id,
            'libelle' => $libelle,
            'montant' => $montant,
            'date_echeance' => now()->addDays(30),
            'statut' => 'en_attente',
            'type_frais' => 'autre',
            'paiement_par_tranches' => false,
            'actif' => true,
        ]);
    }

    private function fraisExisteDeja(int $eleveId, string $typeFrais): bool
    {
        return FraisScolarite::where('eleve_id', $eleveId)
            ->where('type_frais', $typeFrais)
            ->where('statut', '!=', 'annule')
            ->exists();
    }

    /**
     * Montant des frais de réinscription (aligné sur l'affichage /tarifs).
     */
    private function montantFraisReinscription(TarifClasse $tarif): float
    {
        if ((float) $tarif->frais_reinscription > 0) {
            return (float) $tarif->frais_reinscription;
        }

        return round((float) $tarif->frais_inscription * 0.5, 2);
    }

    private function statutDepuisTotaux(array $totaux): string
    {
        if (!array_key_exists('reste_a_payer', $totaux)) {
            return 'payee';
        }

        return ((float) $totaux['reste_a_payer']) > 0.01 ? 'en_cours' : 'payee';
    }

    /**
     * Mois facturables de l'année scolaire (octobre → juin) pour les filtres UI.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getMoisFacturationOptions(AnneeScolaire $anneeScolaire): array
    {
        return $this->moisAnneeScolaireFacturation($anneeScolaire)
            ->map(fn (Carbon $mois) => [
                'value' => $mois->format('Y-m'),
                'label' => $this->libelleMoisCourt($mois),
            ])
            ->values()
            ->all();
    }

    /**
     * Élèves ayant au moins un mois impayé parmi la sélection.
     *
     * @param  array<string>  $moisYm  Format Y-m (ex. 2025-10)
     */
    public function rechercherElevesImpayes(
        AnneeScolaire $anneeScolaire,
        array $moisYm,
        ?int $classeId = null,
        string $typeFrais = 'scolarite'
    ): Collection {
        if ($moisYm === []) {
            return collect();
        }

        $moisSet = collect($moisYm)
            ->map(fn (string $m) => Carbon::parse($m . '-01')->format('Y-m'))
            ->unique()
            ->values()
            ->all();

        $query = Eleve::with(['utilisateur', 'classe'])
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->where('actif', true)
            ->where('exempte_frais', false);

        if ($classeId) {
            $query->where('classe_id', $classeId);
        }

        $resultats = collect();

        foreach ($query->get() as $eleve) {
            $lignes = collect($this->getLignesDisponibles($eleve, $anneeScolaire))
                ->where('type_frais', $typeFrais)
                ->filter(function (array $ligne) use ($moisSet) {
                    return in_array(Carbon::parse($ligne['mois'])->format('Y-m'), $moisSet, true);
                });

            if ($lignes->isEmpty()) {
                continue;
            }

            $moisImpayes = $lignes->map(function (array $ligne) {
                $mois = Carbon::parse($ligne['mois']);

                return [
                    'mois' => $mois->format('Y-m'),
                    'libelle_mois' => $ligne['libelle_mois'] ?? self::formatLibelleMois($mois),
                    'libelle' => $ligne['libelle'],
                    'montant' => (float) $ligne['montant'],
                    'partiel' => (bool) ($ligne['partiel'] ?? false),
                ];
            })->sortBy('mois')->values();

            $resultats->push([
                'eleve' => $eleve,
                'classe' => $eleve->classe,
                'mois_impayes' => $moisImpayes,
                'nombre_mois' => $moisImpayes->count(),
                'total_du' => round($moisImpayes->sum('montant'), 2),
            ]);
        }

        return $resultats->sortBy(function (array $item) {
            $u = $item['eleve']->utilisateur;

            return mb_strtolower(trim(($u->nom ?? '') . ' ' . ($u->prenom ?? '')));
        })->values();
    }

    public static function formatLibelleMois(Carbon $mois): string
    {
        $nomsMois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return ($nomsMois[(int) $mois->format('n')] ?? $mois->format('m')) . ' ' . $mois->format('Y');
    }

    private function libelleMoisCourt(Carbon $mois): string
    {
        return self::formatLibelleMois($mois);
    }

    /**
     * Réconcilie toutes les tranches d'un frais (remises facture incluses).
     */
    public function reconcilierTranchesFrais(FraisScolarite $frais): void
    {
        $frais->loadMissing('tranchesPaiement');

        foreach ($frais->tranchesPaiement as $tranche) {
            $this->reconcilierTrancheSiSoldée($tranche);
        }
    }

    /**
     * Reste dû sur une tranche en tenant compte des remises déjà accordées sur factures payées.
     */
    public function resteEffectifTranche(TranchePaiement $tranche): float
    {
        $montantTranche = (float) $tranche->montant_tranche;
        $montantPaye = (float) $tranche->montant_paye;

        $remiseFacture = (float) FactureLigne::query()
            ->where('tranche_paiement_id', $tranche->id)
            ->whereHas('facture', fn ($q) => $q->where('statut', 'payee'))
            ->get(['montant_brut', 'montant_net', 'montant_remise', 'facture_id'])
            ->sum(fn (FactureLigne $ligne) => max(
                0,
                round((float) $ligne->montant_brut - (float) $ligne->montant_net, 2)
            ) + max(0, (float) $ligne->montant_remise));

        $remiseFacture += $this->remiseGlobaleFactureSurTranche($tranche);

        return max(0, round($montantTranche - $montantPaye - $remiseFacture, 2));
    }

    /**
     * Remise globale de facture non ventilée sur les lignes (anciennes factures ou paiement partiel FIFO).
     */
    private function remiseGlobaleFactureSurTranche(TranchePaiement $tranche): float
    {
        $lignes = FactureLigne::query()
            ->where('tranche_paiement_id', $tranche->id)
            ->whereHas('facture', fn ($q) => $q->where('statut', 'payee')->where('montant_remise', '>', 0))
            ->with('facture.lignes')
            ->get();

        $total = 0.0;

        foreach ($lignes->groupBy('facture_id') as $groupe) {
            $facture = $groupe->first()->facture;
            if (!$facture) {
                continue;
            }

            $alloueeSurFacture = $facture->lignes->sum(
                fn (FactureLigne $l) => max(0, round((float) $l->montant_brut - (float) $l->montant_net, 2))
                    + max(0, (float) $l->montant_remise)
            );
            $disponible = max(0, round((float) $facture->montant_remise - $alloueeSurFacture, 2));

            if ($disponible <= 0) {
                continue;
            }

            $ligneTranche = $groupe->first();
            $brutMois = (float) $tranche->montant_tranche;
            $netVerse = (float) $ligneTranche->montant_net;

            if ($netVerse + 0.01 >= $brutMois) {
                continue;
            }

            $manque = round($brutMois - (float) $tranche->montant_paye, 2);
            if ($manque <= 0) {
                continue;
            }

            $total += min($disponible, $manque);
        }

        return round($total, 2);
    }

    /**
     * Corrige les tranches soldées via facture (remise incluse) mais encore marquées partielles.
     */
    public function reconcilierTrancheSiSoldée(TranchePaiement $tranche): TranchePaiement
    {
        if ($tranche->statut === 'paye') {
            return $tranche;
        }

        $reste = $this->resteEffectifTranche($tranche);
        if ($reste > 0.01) {
            return $tranche;
        }

        $tranche->update([
            'statut' => 'paye',
            'montant_paye' => $tranche->montant_tranche,
        ]);

        return $tranche->fresh();
    }
}
