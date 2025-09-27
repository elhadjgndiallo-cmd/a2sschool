<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranchePaiement extends Model
{
    use HasFactory;

    protected $table = 'tranches_paiement';

    protected $fillable = [
        'frais_scolarite_id',
        'numero_tranche',
        'montant_tranche',
        'date_echeance',
        'statut',
        'montant_paye',
        'date_paiement',
        'observations'
    ];

    protected $casts = [
        'montant_tranche' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_echeance' => 'date',
        'date_paiement' => 'date'
    ];

    /**
     * Relation avec les frais de scolarité
     */
    public function fraisScolarite()
    {
        return $this->belongsTo(FraisScolarite::class);
    }

    /**
     * Relation avec les paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Accessor pour le montant restant à payer
     */
    public function getMontantRestantAttribute()
    {
        return $this->montant_tranche - $this->montant_paye;
    }

    /**
     * Scope pour les tranches en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les tranches payées
     */
    public function scopePayees($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Scope pour les tranches en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    /**
     * Vérifier si la tranche est en retard
     */
    public function isEnRetard()
    {
        return $this->date_echeance < now()->toDateString() && $this->statut !== 'paye';
    }

    /**
     * Marquer la tranche comme payée
     */
    public function marquerCommePayee($montant, $datePaiement = null)
    {
        $this->update([
            'statut' => 'paye',
            'montant_paye' => $montant,
            'date_paiement' => $datePaiement ?? now()->toDateString()
        ]);
    }
}
