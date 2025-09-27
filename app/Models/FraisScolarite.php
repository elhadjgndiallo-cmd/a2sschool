<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraisScolarite extends Model
{
    use HasFactory;

    protected $table = 'frais_scolarite';

    protected $fillable = [
        'eleve_id',
        'libelle',
        'montant',
        'date_echeance',
        'statut',
        'type_frais',
        'description',
        'paiement_par_tranches',
        'nombre_tranches',
        'montant_tranche',
        'periode_tranche',
        'date_debut_tranches',
        'calendrier_tranches'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_echeance' => 'date',
        'montant_tranche' => 'decimal:2',
        'date_debut_tranches' => 'date',
        'calendrier_tranches' => 'array',
        'paiement_par_tranches' => 'boolean'
    ];

    /**
     * Relation avec l'élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    /**
     * Relation avec les paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Relation avec les tranches de paiement
     */
    public function tranchesPaiement()
    {
        return $this->hasMany(TranchePaiement::class);
    }

    /**
     * Scope pour les frais en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Accessor pour le montant total payé
     */
    public function getMontantPayeAttribute()
    {
        if ($this->paiement_par_tranches) {
            return $this->tranchesPaiement->sum('montant_paye');
        } else {
            return $this->paiements->sum('montant_paye');
        }
    }

    /**
     * Accessor pour le montant restant à payer
     */
    public function getMontantRestantAttribute()
    {
        return $this->montant - $this->montant_paye;
    }

    /**
     * Créer les tranches de paiement
     */
    public function creerTranchesPaiement()
    {
        if (!$this->paiement_par_tranches || !$this->nombre_tranches) {
            return false;
        }

        $montantParTranche = $this->montant / $this->nombre_tranches;
        $dateDebut = $this->date_debut_tranches ?? $this->date_echeance;
        
        for ($i = 1; $i <= $this->nombre_tranches; $i++) {
            $dateEcheance = $this->calculerDateEcheance($dateDebut, $i);
            
            TranchePaiement::create([
                'frais_scolarite_id' => $this->id,
                'numero_tranche' => $i,
                'montant_tranche' => $montantParTranche,
                'date_echeance' => $dateEcheance,
                'statut' => 'en_attente'
            ]);
        }
        
        return true;
    }

    /**
     * Calculer la date d'échéance selon la période
     */
    private function calculerDateEcheance($dateDebut, $numeroTranche)
    {
        $date = \Carbon\Carbon::parse($dateDebut);
        
        switch ($this->periode_tranche) {
            case 'mensuel':
                return $date->addMonths($numeroTranche - 1)->toDateString();
            case 'trimestriel':
                return $date->addMonths(($numeroTranche - 1) * 3)->toDateString();
            case 'semestriel':
                return $date->addMonths(($numeroTranche - 1) * 6)->toDateString();
            case 'annuel':
                return $date->addYears($numeroTranche - 1)->toDateString();
            default:
                return $date->addMonths($numeroTranche - 1)->toDateString();
        }
    }

    /**
     * Vérifier si toutes les tranches sont payées
     */
    public function toutesTranchesPayees()
    {
        if (!$this->paiement_par_tranches) {
            return false;
        }
        
        return $this->tranchesPaiement()->where('statut', '!=', 'paye')->count() === 0;
    }
}
