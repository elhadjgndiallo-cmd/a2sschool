<?php

namespace App\Console\Commands;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\RecuRappel;
use App\Models\TarifClasse;
use App\Models\TranchePaiement;
use App\Services\FacturationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FusionnerTransportVersScolarite extends Command
{
    protected $signature = 'frais:fusionner-transport-scolarite {--dry-run : Simuler sans écrire}';

    protected $description = 'Fusionne les frais transport (9 mois auto) dans la scolarité, conserve les mois payés et valide les factures.';

    public function handle(FacturationService $facturation): int
    {
        $dry = (bool) $this->option('dry-run');
        $annee = AnneeScolaire::anneeActive();
        if (!$annee) {
            $this->error('Aucune année scolaire active.');

            return self::FAILURE;
        }

        $this->info($dry ? 'Simulation (aucune écriture).' : 'Fusion transport → scolarité…');

        $stats = [
            'tarifs' => 0,
            'convertis' => 0,
            'fusionnes' => 0,
            'mois_valides' => 0,
            'factures_validees' => 0,
            'supprimes' => 0,
        ];

        DB::beginTransaction();
        try {
            $stats['tarifs'] = $this->corrigerTarifsScolarite();

            $transports = FraisScolarite::query()
                ->where('type_frais', 'transport')
                ->where('statut', '!=', 'annule')
                ->with(['eleve.classe', 'eleve.utilisateur', 'tranchesPaiement.paiements'])
                ->get();

            foreach ($transports as $fraisTransport) {
                $eleve = $fraisTransport->eleve;
                if (!$eleve) {
                    continue;
                }

                $montantMensuel = $this->montantMensuelScolarite($eleve, $annee);
                $fraisScolarite = FraisScolarite::query()
                    ->where('eleve_id', $eleve->id)
                    ->where('type_frais', 'scolarite')
                    ->where('statut', '!=', 'annule')
                    ->with(['tranchesPaiement.paiements'])
                    ->first();

                if (!$fraisScolarite) {
                    $this->convertirTransportEnScolarite($fraisTransport, $eleve, $annee, $montantMensuel);
                    $stats['convertis']++;
                    $this->line('  Converti  '.$this->nomEleve($eleve).' (transport → scolarité)');
                    continue;
                }

                $moisValides = $this->fusionnerVersScolarite(
                    $fraisScolarite,
                    $fraisTransport,
                    $montantMensuel,
                    $facturation
                );
                $stats['fusionnes']++;
                $stats['mois_valides'] += $moisValides;
                $this->line('  Fusionné '.$this->nomEleve($eleve).' — '.$moisValides.' mois validé(s)');
            }

            $stats['factures_validees'] = $this->validerFacturesMoisPayes(
                $transports->pluck('eleve_id')->unique()->filter()->all()
            );
            $stats['supprimes'] = $this->supprimerTousLesFraisTransport();

            if ($dry) {
                DB::rollBack();
                $this->warn('Simulation : rollback.');
            } else {
                DB::commit();
                $this->info('Fusion enregistrée.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Étape', 'Nombre'],
            [
                ['Tarifs corrigés (transport → scolarité)', $stats['tarifs']],
                ['Transport converti (sans scolarité)', $stats['convertis']],
                ['Élèves fusionnés', $stats['fusionnes']],
                ['Mois scolarité validés depuis transport', $stats['mois_valides']],
                ['Factures passées en payée', $stats['factures_validees']],
                ['Frais transport supprimés', $stats['supprimes']],
            ]
        );

        return self::SUCCESS;
    }

    private function corrigerTarifsScolarite(): int
    {
        $tarifs = TarifClasse::query()
            ->where('actif', true)
            ->where('frais_scolarite_mensuel', '<=', 0.01)
            ->where('frais_transport_mensuel', '>', 0.01)
            ->get();

        foreach ($tarifs as $tarif) {
            $tarif->update([
                'frais_scolarite_mensuel' => $tarif->frais_transport_mensuel,
                'frais_transport_mensuel' => 0,
            ]);
        }

        return $tarifs->count();
    }

    private function montantMensuelScolarite(Eleve $eleve, AnneeScolaire $annee): float
    {
        $tarif = TarifClasse::query()
            ->where('classe_id', $eleve->classe_id)
            ->where('annee_scolaire', $annee->nom)
            ->where('actif', true)
            ->first()
            ?? TarifClasse::query()
                ->where('classe_id', $eleve->classe_id)
                ->where('actif', true)
                ->orderByDesc('id')
                ->first();

        $montant = $tarif ? (float) $tarif->frais_scolarite_mensuel : 0.0;
        if ($montant > 0.01) {
            return $montant;
        }

        if ($tarif && (float) $tarif->frais_transport_mensuel > 0.01) {
            return (float) $tarif->frais_transport_mensuel;
        }

        return 170000.0;
    }

    private function convertirTransportEnScolarite(
        FraisScolarite $fraisTransport,
        Eleve $eleve,
        AnneeScolaire $annee,
        float $montantMensuel
    ): void {
        $classeNom = $eleve->classe?->nom ?? 'Classe';
        $nombreMois = max(1, (int) $fraisTransport->nombre_tranches ?: $fraisTransport->tranchesPaiement->count() ?: 9);

        $fraisTransport->update([
            'type_frais' => 'scolarite',
            'libelle' => 'Scolarité - '.$classeNom.' - '.$annee->nom,
            'montant' => round($montantMensuel * $nombreMois, 2),
            'montant_tranche' => $montantMensuel,
        ]);

        foreach ($fraisTransport->tranchesPaiement as $tranche) {
            $tranche->update(['montant_tranche' => $montantMensuel]);
        }

        FactureLigne::query()
            ->where('frais_scolarite_id', $fraisTransport->id)
            ->where('type_frais', 'transport')
            ->update(['type_frais' => 'scolarite']);
    }

    private function fusionnerVersScolarite(
        FraisScolarite $fraisScolarite,
        FraisScolarite $fraisTransport,
        float $montantMensuel,
        FacturationService $facturation
    ): int {
        $nombreMois = max(9, (int) $fraisScolarite->nombre_tranches ?: 9);
        $fraisScolarite->update([
            'montant' => round($montantMensuel * $nombreMois, 2),
            'montant_tranche' => $montantMensuel,
        ]);

        $tranchesSco = $fraisScolarite->tranchesPaiement->keyBy(
            fn (TranchePaiement $t) => Carbon::parse($t->date_echeance)->format('Y-m')
        );
        $tranchesTra = $fraisTransport->tranchesPaiement->keyBy(
            fn (TranchePaiement $t) => Carbon::parse($t->date_echeance)->format('Y-m')
        );

        $moisValides = 0;
        $facturesAValider = collect();

        foreach ($tranchesTra as $ym => $trancheTra) {
            $trancheSco = $tranchesSco->get($ym);
            if (!$trancheSco) {
                continue;
            }

            $trancheSco->update(['montant_tranche' => $montantMensuel]);
            $scoPayee = $this->trancheEstPayee($trancheSco, $montantMensuel);
            $traPayee = $this->trancheEstPayee($trancheTra, $montantMensuel);

            if ($traPayee && !$scoPayee) {
                Paiement::query()
                    ->where('tranche_paiement_id', $trancheTra->id)
                    ->update([
                        'tranche_paiement_id' => $trancheSco->id,
                        'frais_scolarite_id' => $fraisScolarite->id,
                    ]);

                FactureLigne::query()
                    ->where('tranche_paiement_id', $trancheTra->id)
                    ->update([
                        'tranche_paiement_id' => $trancheSco->id,
                        'frais_scolarite_id' => $fraisScolarite->id,
                        'type_frais' => 'scolarite',
                    ]);

                $trancheSco->update([
                    'statut' => 'paye',
                    'montant_paye' => $montantMensuel,
                    'date_paiement' => $trancheTra->date_paiement ?? $trancheSco->date_paiement ?? now()->toDateString(),
                ]);
                $moisValides++;
            } elseif ($traPayee && $scoPayee) {
                FactureLigne::query()
                    ->where('tranche_paiement_id', $trancheTra->id)
                    ->update(['type_frais' => 'scolarite']);
            }

            $ids = FactureLigne::query()
                ->where(function ($q) use ($trancheSco, $trancheTra) {
                    $q->where('tranche_paiement_id', $trancheSco->id)
                        ->orWhere('tranche_paiement_id', $trancheTra->id);
                })
                ->pluck('facture_id');
            $facturesAValider = $facturesAValider->merge($ids);
        }

        $this->rattacherPuisSupprimerTransport($fraisScolarite, $fraisTransport);

        $fraisScolarite->unsetRelation('tranchesPaiement');
        $fraisScolarite->load('tranchesPaiement');
        $facturation->reconcilierTranchesFrais($fraisScolarite);

        if ($fraisScolarite->tranchesPaiement->every(fn (TranchePaiement $t) => $t->fresh()->statut === 'paye')) {
            $fraisScolarite->update(['statut' => 'paye']);
        }

        $this->validerFacturesSiSoldées($facturesAValider->unique()->filter()->all());

        return $moisValides;
    }

    private function rattacherPuisSupprimerTransport(
        FraisScolarite $fraisScolarite,
        FraisScolarite $fraisTransport
    ): void {
        $fraisScolarite->loadMissing('tranchesPaiement');
        $fraisTransport->loadMissing('tranchesPaiement');

        $tranchesSco = $fraisScolarite->tranchesPaiement->keyBy(
            fn (TranchePaiement $t) => Carbon::parse($t->date_echeance)->format('Y-m')
        );
        $fallbackSco = $fraisScolarite->tranchesPaiement->first();

        foreach ($fraisTransport->tranchesPaiement as $trancheTra) {
            $ym = Carbon::parse($trancheTra->date_echeance)->format('Y-m');
            $trancheSco = $tranchesSco->get($ym) ?? $fallbackSco;
            if (!$trancheSco) {
                continue;
            }

            Paiement::query()
                ->where(function ($q) use ($trancheTra, $fraisTransport) {
                    $q->where('tranche_paiement_id', $trancheTra->id)
                        ->orWhere(function ($q2) use ($fraisTransport, $trancheTra) {
                            $q2->where('frais_scolarite_id', $fraisTransport->id)
                                ->where('tranche_paiement_id', $trancheTra->id);
                        });
                })
                ->update([
                    'tranche_paiement_id' => $trancheSco->id,
                    'frais_scolarite_id' => $fraisScolarite->id,
                ]);

            FactureLigne::query()
                ->where(function ($q) use ($trancheTra, $fraisTransport) {
                    $q->where('tranche_paiement_id', $trancheTra->id)
                        ->orWhere(function ($q2) use ($fraisTransport, $trancheTra) {
                            $q2->where('frais_scolarite_id', $fraisTransport->id)
                                ->where('tranche_paiement_id', $trancheTra->id);
                        });
                })
                ->update([
                    'tranche_paiement_id' => $trancheSco->id,
                    'frais_scolarite_id' => $fraisScolarite->id,
                    'type_frais' => 'scolarite',
                ]);
        }

        Paiement::query()
            ->where('frais_scolarite_id', $fraisTransport->id)
            ->update([
                'frais_scolarite_id' => $fraisScolarite->id,
                'tranche_paiement_id' => $fallbackSco?->id,
            ]);

        FactureLigne::query()
            ->where('frais_scolarite_id', $fraisTransport->id)
            ->update([
                'frais_scolarite_id' => $fraisScolarite->id,
                'type_frais' => 'scolarite',
            ]);

        RecuRappel::query()
            ->where('frais_scolarite_id', $fraisTransport->id)
            ->update(['frais_scolarite_id' => $fraisScolarite->id]);

        TranchePaiement::query()->where('frais_scolarite_id', $fraisTransport->id)->delete();
        $fraisTransport->delete();
    }

    private function supprimerTousLesFraisTransport(): int
    {
        $transports = FraisScolarite::query()
            ->where('type_frais', 'transport')
            ->with(['tranchesPaiement', 'eleve'])
            ->get();

        $n = 0;
        foreach ($transports as $fraisTransport) {
            $fraisScolarite = FraisScolarite::query()
                ->where('eleve_id', $fraisTransport->eleve_id)
                ->where('type_frais', 'scolarite')
                ->where('statut', '!=', 'annule')
                ->with('tranchesPaiement')
                ->first();

            if (!$fraisScolarite) {
                $this->convertirTransportEnScolarite(
                    $fraisTransport,
                    $fraisTransport->eleve,
                    AnneeScolaire::anneeActive(),
                    $this->montantMensuelScolarite($fraisTransport->eleve, AnneeScolaire::anneeActive())
                );
                continue;
            }

            $this->rattacherPuisSupprimerTransport($fraisScolarite, $fraisTransport);
            $n++;
        }

        FactureLigne::query()->where('type_frais', 'transport')->update(['type_frais' => 'scolarite']);

        return $n;
    }

    /**
     * @param  array<int, int>  $eleveIds
     */
    private function validerFacturesMoisPayes(array $eleveIds): int
    {
        if ($eleveIds === []) {
            return 0;
        }

        $ids = Facture::query()
            ->whereIn('eleve_id', $eleveIds)
            ->where('statut', 'en_cours')
            ->pluck('id')
            ->all();

        return $this->validerFacturesSiSoldées($ids);
    }

    /**
     * @param  array<int, int>  $factureIds
     */
    private function validerFacturesSiSoldées(array $factureIds): int
    {
        $factureIds = array_values(array_unique(array_filter($factureIds)));
        if ($factureIds === []) {
            return 0;
        }

        $count = 0;
        $factures = Facture::query()
            ->whereIn('id', $factureIds)
            ->where('statut', 'en_cours')
            ->with(['lignes.tranchePaiement'])
            ->get();

        foreach ($factures as $facture) {
            if (!$this->factureEstSoldee($facture)) {
                continue;
            }

            $facture->update(['statut' => 'payee']);
            $count++;
        }

        return $count;
    }

    private function factureEstSoldee(Facture $facture): bool
    {
        if ($facture->lignes->isEmpty()) {
            return false;
        }

        return $facture->lignes->every(function (FactureLigne $ligne) {
            if ($ligne->paiement_id) {
                return true;
            }

            $tranche = $ligne->tranchePaiement;
            if (!$tranche) {
                return false;
            }

            return $tranche->statut === 'paye'
                || (float) $tranche->montant_paye + 0.01 >= (float) $tranche->montant_tranche;
        });
    }

    private function trancheEstPayee(TranchePaiement $tranche, float $montantMensuel): bool
    {
        if ($tranche->statut === 'paye') {
            return true;
        }

        $cash = round((float) $tranche->paiements->sum('montant_paye'), 2);
        if ($cash + 0.01 >= $montantMensuel) {
            return true;
        }

        return (float) $tranche->montant_paye + 0.01 >= $montantMensuel;
    }

    private function nomEleve(Eleve $eleve): string
    {
        $u = $eleve->utilisateur;

        return trim(($eleve->numero_etudiant ?? '').' '.($u->prenom ?? '').' '.($u->nom ?? ''));
    }
}
