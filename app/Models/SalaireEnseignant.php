<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaireEnseignant extends Model
{
    use HasFactory;

    protected $table = 'salaires_enseignants';

    protected $fillable = [
        'enseignant_id',
        'periode_debut',
        'periode_fin',
        'nombre_heures',
        'taux_horaire',
        'salaire_base',
        'prime_anciennete',
        'prime_performance',
        'prime_heures_supplementaires',
        'deduction_absences',
        'deduction_autres',
        'deduction_avances',
        'salaire_brut',
        'salaire_net',
        'statut',
        'observations',
        'calcule_par',
        'valide_par',
        'paye_par',
        'date_calcul',
        'date_validation',
        'date_paiement'
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'nombre_heures' => 'integer',
        'taux_horaire' => 'decimal:2',
        'salaire_base' => 'decimal:2',
        'prime_anciennete' => 'decimal:2',
        'prime_performance' => 'decimal:2',
        'prime_heures_supplementaires' => 'decimal:2',
        'deduction_absences' => 'decimal:2',
        'deduction_autres' => 'decimal:2',
        'deduction_avances' => 'decimal:2',
        'salaire_brut' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'date_calcul' => 'date',
        'date_validation' => 'date',
        'date_paiement' => 'date'
    ];

    /**
     * Relation avec l'enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Relation avec l'utilisateur qui a calculé
     */
    public function calculePar()
    {
        return $this->belongsTo(Utilisateur::class, 'calcule_par');
    }

    /**
     * Relation avec l'utilisateur qui a validé
     */
    public function validePar()
    {
        return $this->belongsTo(Utilisateur::class, 'valide_par');
    }

    /**
     * Relation avec l'utilisateur qui a payé
     */
    public function payePar()
    {
        return $this->belongsTo(Utilisateur::class, 'paye_par');
    }

    public function bonsAvance()
    {
        return $this->hasMany(BonSalaireEnseignant::class, 'salaire_enseignant_id');
    }

    /**
     * Calculer le salaire brut
     */
    public function calculerSalaireBrut()
    {
        // Calculate hourly salary only if both hours and rate are provided
        $salaireHoraire = ($this->nombre_heures && $this->taux_horaire) ? 
                          $this->nombre_heures * $this->taux_horaire : 0;
        $this->salaire_brut = $this->salaire_base + $salaireHoraire + 
                             $this->prime_anciennete + $this->prime_performance + 
                             $this->prime_heures_supplementaires;
        return $this->salaire_brut;
    }

    /**
     * Calculer le salaire net
     */
    public function calculerSalaireNet()
    {
        $this->salaire_net = max(0, round(
            (float) $this->salaire_brut
            - (float) $this->deduction_absences
            - (float) $this->deduction_autres
            - (float) ($this->deduction_avances ?? 0),
            2
        ));

        return $this->salaire_net;
    }

    public function libererAvancesLiees(): void
    {
        BonSalaireEnseignant::where('salaire_enseignant_id', $this->id)
            ->where('statut', 'deduit')
            ->update([
                'statut' => 'actif',
                'salaire_enseignant_id' => null,
                'deduit_le' => null,
            ]);
    }

    public function appliquerAvances(): void
    {
        $this->libererAvancesLiees();

        $enseignant = $this->enseignant ?? Enseignant::find($this->enseignant_id);
        if (!$enseignant) {
            $this->deduction_avances = 0;
            return;
        }

        $anneeId = $enseignant->annee_scolaire_id;

        $query = BonSalaireEnseignant::actifs()
            ->pourEnseignant($this->enseignant_id)
            ->orderBy('date_bon')
            ->orderBy('id');

        if ($anneeId) {
            $query->where(function ($q) use ($anneeId) {
                $q->where('annee_scolaire_id', $anneeId)->orWhereNull('annee_scolaire_id');
            });
        }

        $avances = $query->get();
        if ($avances->isEmpty()) {
            $this->deduction_avances = 0;
            return;
        }

        $plafond = max(0, round(
            (float) $this->salaire_brut
            - (float) $this->deduction_absences
            - (float) $this->deduction_autres,
            2
        ));

        $totalAvances = 0;

        foreach ($avances as $avance) {
            if ($totalAvances >= $plafond) {
                break;
            }

            $reste = round($plafond - $totalAvances, 2);
            $montantDeduit = round(min((float) $avance->montant, $reste), 2);

            if ($montantDeduit <= 0) {
                continue;
            }

            $totalAvances = round($totalAvances + $montantDeduit, 2);

            BonSalaireEnseignant::where('id', $avance->id)->update([
                'statut' => 'deduit',
                'salaire_enseignant_id' => $this->id,
                'deduit_le' => now()->toDateString(),
            ]);
        }

        $this->deduction_avances = $totalAvances;
    }

    /**
     * Calculer automatiquement les salaires
     */
    public function calculerSalaires()
    {
        $this->calculerSalaireBrut();
        $this->appliquerAvances();
        $this->calculerSalaireNet();
        $this->statut = 'calculé';
        $this->date_calcul = now()->toDateString();
        $this->calcule_par = auth()->id();
        $this->save();
    }

    /**
     * Valider le salaire
     */
    public function valider()
    {
        $this->update([
            'statut' => 'validé',
            'date_validation' => now()->toDateString(),
            'valide_par' => auth()->id()
        ]);
    }

    /**
     * Marquer comme payé
     */
    public function marquerCommePaye($datePaiement = null)
    {
        $this->update([
            'statut' => 'payé',
            'date_paiement' => $datePaiement ? $datePaiement : now()->toDateString(),
            'paye_par' => auth()->id()
        ]);
    }

    /**
     * Scope pour les salaires calculés
     */
    public function scopeCalcules($query)
    {
        return $query->where('statut', 'calculé');
    }

    /**
     * Scope pour les salaires validés
     */
    public function scopeValides($query)
    {
        return $query->where('statut', 'validé');
    }

    /**
     * Scope pour les salaires payés
     */
    public function scopePayes($query)
    {
        return $query->where('statut', 'payé');
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('periode_debut', [$dateDebut, $dateFin]);
    }

    /**
     * Accessor pour le libellé du statut
     */
    public function getStatutLibelleAttribute()
    {
        $statuts = [
            'calculé' => 'Calculé',
            'validé' => 'Validé',
            'payé' => 'Payé',
            'annulé' => 'Annulé'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Accessor pour la période formatée
     */
    public function getPeriodeFormateeAttribute()
    {
        return $this->periode_debut->format('d/m/Y') . ' - ' . $this->periode_fin->format('d/m/Y');
    }
}
