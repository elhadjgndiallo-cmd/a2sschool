<?php

namespace App\Services;

use App\Models\Entree;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;

class PaiementScolariteService
{
    public function enregistrerPaiementTranche(
        TranchePaiement $tranche,
        float $montantPaye,
        string $datePaiement,
        string $modePaiement,
        ?string $referencePaiement,
        ?string $observations,
        int $encaissePar,
        bool $creerEntreeComptable = true,
        float $montantRemise = 0
    ): Paiement {
        $tranche->refresh();
        $reste = (float) $tranche->montant_tranche - (float) $tranche->montant_paye;
        $montantRemise = max(0, round($montantRemise, 2));
        $creditTranche = round($montantPaye + $montantRemise, 2);

        if ($montantPaye <= 0) {
            throw new \InvalidArgumentException('Le montant payé doit être supérieur à zéro.');
        }

        if ($creditTranche - $reste > 0.00001) {
            throw new \InvalidArgumentException('Le montant dépasse le reste dû sur la tranche.');
        }

        if ($tranche->statut === 'paye') {
            throw new \InvalidArgumentException('Cette tranche est déjà payée.');
        }

        $paiement = Paiement::create([
            'frais_scolarite_id' => $tranche->frais_scolarite_id,
            'tranche_paiement_id' => $tranche->id,
            'montant_paye' => $montantPaye,
            'date_paiement' => $datePaiement,
            'mode_paiement' => $modePaiement,
            'reference_paiement' => $referencePaiement,
            'observations' => $observations,
            'encaisse_par' => $encaissePar,
        ]);

        // La remise réduit l'obligation sur la tranche (le mois est soldé au net, pas au brut encaissé)
        $nouveauMontantPaye = (float) $tranche->montant_paye + $creditTranche;
        $tranche->update([
            'montant_paye' => $nouveauMontantPaye,
            'date_paiement' => $datePaiement,
            'statut' => $nouveauMontantPaye + 0.00001 >= (float) $tranche->montant_tranche ? 'paye' : 'en_attente',
        ]);

        $frais = $tranche->fraisScolarite()->first();
        if ($frais && $frais->toutesTranchesPayees()) {
            $frais->update(['statut' => 'paye']);
        }

        if ($creerEntreeComptable) {
            $this->creerEntreeComptable($paiement, $frais);
        }

        return $paiement;
    }

    /**
     * Une seule entrée comptable pour l'ensemble d'une facture multi-mois.
     */
    public function creerEntreeComptableFacture(Facture $facture): Entree
    {
        return $this->mettreAJourEntreeComptableFacture($facture);
    }

    public function mettreAJourEntreeComptableFacture(Facture $facture): Entree
    {
        $facture->load(['eleve.utilisateur', 'eleve.classe', 'lignes', 'generePar']);
        $payload = $this->donneesEntreeComptableFacture($facture);

        $entree = Entree::where('reference', $facture->numero_facture)->first();

        if ($entree) {
            $entree->update($payload);

            return $entree->fresh();
        }

        return Entree::create($payload);
    }

    public function supprimerEntreeComptableFacture(Facture $facture): void
    {
        Entree::where('reference', $facture->numero_facture)->delete();
    }

    /**
     * Annule le paiement d'une ligne de facture et recrédite la tranche.
     */
    public function annulerPaiementFactureLigne(FactureLigne $ligne): void
    {
        $ligne->loadMissing(['paiement', 'tranchePaiement', 'fraisScolarite']);

        $paiement = $ligne->paiement;
        if (!$paiement) {
            return;
        }

        $tranche = $ligne->tranchePaiement ?? $paiement->tranchePaiement;
        if ($tranche) {
            $creditTranche = round((float) $ligne->montant_brut, 2);
            $nouveauMontantPaye = max(0, round((float) $tranche->montant_paye - $creditTranche, 2));

            $tranche->update([
                'montant_paye' => $nouveauMontantPaye,
                'statut' => $nouveauMontantPaye + 0.00001 >= (float) $tranche->montant_tranche ? 'paye' : 'en_attente',
                'date_paiement' => $nouveauMontantPaye > 0 ? $tranche->date_paiement : null,
            ]);
        }

        if ($ligne->fraisScolarite) {
            $this->recalculerStatutFrais($ligne->fraisScolarite);
        }

        $paiement->delete();
    }

    public function recalculerStatutFrais(FraisScolarite $frais): void
    {
        $frais->refresh();

        if ($frais->toutesTranchesPayees()) {
            $frais->update(['statut' => 'paye']);

            return;
        }

        if ($frais->date_echeance && $frais->date_echeance < now()) {
            $frais->update(['statut' => 'en_retard']);

            return;
        }

        $frais->update(['statut' => 'en_attente']);
    }

    private function donneesEntreeComptableFacture(Facture $facture): array
    {
        $eleve = $facture->eleve;
        $classe = $eleve->classe;
        $libellesMois = $facture->lignes->pluck('libelle')->implode(', ');

        $eleveNom = trim(($eleve->utilisateur->prenom ?? '') . ' ' . ($eleve->utilisateur->nom ?? ''));
        $matricule = $eleve->numero_etudiant ?? 'N/A';
        $nomClasse = $classe->nom ?? 'N/A';

        $description = 'Paiement frais scolarité - ' . $eleveNom
            . ' (Mat: ' . $matricule . ', Classe: ' . $nomClasse . ')';

        $libelle = 'Facture ' . $facture->numero_facture . ' — '
            . number_format((float) $facture->total, 0, ',', ' ') . ' GNF'
            . ($libellesMois ? ' — ' . $libellesMois : '');

        return [
            'libelle' => $libelle,
            'description' => $description,
            'montant' => $facture->total,
            'date_entree' => $facture->date_facture,
            'source' => 'Paiements scolaires',
            'mode_paiement' => $facture->mode_paiement,
            'reference' => $facture->numero_facture,
            'enregistre_par' => $facture->genere_par,
        ];
    }

    public function creerEntreeComptable(Paiement $paiement, FraisScolarite $frais): Entree
    {
        $frais->loadMissing(['eleve.utilisateur', 'eleve.classe']);
        $eleve = $frais->eleve;
        $classe = $eleve->classe;

        $typeFrais = ucfirst($frais->type_frais);
        if ($frais->type_frais === 'scolarite') {
            $typeFrais = 'Scolarité';
        } elseif ($frais->type_frais === 'inscription') {
            $typeFrais = 'Inscription';
        } elseif ($frais->type_frais === 'reinscription') {
            $typeFrais = 'Réinscription';
        }

        $libelle = "{$typeFrais} - {$eleve->numero_etudiant}";
        if ($paiement->reference_paiement) {
            $libelle .= " - Ref: {$paiement->reference_paiement}";
        }

        $source = match ($frais->type_frais) {
            'scolarite' => 'Scolarité',
            'inscription' => 'Inscription',
            'reinscription' => 'Réinscription',
            'transport' => 'Transport',
            'cantine' => 'Cantine',
            'uniforme' => 'Uniforme',
            'livres' => 'Livres',
            'autres' => 'Autres frais',
            default => 'Paiements scolaires',
        };

        return Entree::create([
            'libelle' => $libelle,
            'description' => "Paiement de {$paiement->montant_paye} GNF pour les frais de scolarité de l'élève {$eleve->utilisateur->nom} de la classe {$classe->nom}. Référence paiement: {$paiement->reference_paiement}",
            'montant' => $paiement->montant_paye,
            'date_entree' => $paiement->date_paiement,
            'source' => $source,
            'mode_paiement' => $paiement->mode_paiement,
            'reference' => $paiement->reference_paiement,
            'enregistre_par' => $paiement->encaisse_par,
        ]);
    }
}
