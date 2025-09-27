<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'frais_scolarite_id',
        'tranche_paiement_id',
        'montant_paye',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
        'numero_recu',
        'observations',
        'encaisse_par'
    ];

    protected $casts = [
        'montant_paye' => 'decimal:2',
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
     * Relation avec l'utilisateur qui a encaissé
     */
    public function encaissePar()
    {
        return $this->belongsTo(Utilisateur::class, 'encaisse_par');
    }

    /**
     * Relation avec la tranche de paiement
     */
    public function tranchePaiement()
    {
        return $this->belongsTo(TranchePaiement::class);
    }

    /**
     * Boot method pour générer automatiquement le numéro de reçu
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            if (!$paiement->numero_recu) {
                $paiement->numero_recu = static::genererNumeroRecu();
            }
        });
    }

    /**
     * Générer un numéro de reçu unique
     */
    public static function genererNumeroRecu()
    {
        $annee = date('Y');
        $prefixe = "REC-{$annee}-";
        
        // Trouver le dernier numéro de reçu pour cette année
        $dernierRecu = static::where('numero_recu', 'like', $prefixe . '%')
            ->orderBy('numero_recu', 'desc')
            ->first();
        
        if ($dernierRecu) {
            // Extraire le numéro du dernier reçu
            $dernierNumero = (int) substr($dernierRecu->numero_recu, strlen($prefixe));
            $nouveauNumero = $dernierNumero + 1;
        } else {
            $nouveauNumero = 1;
        }
        
        return $prefixe . str_pad($nouveauNumero, 6, '0', STR_PAD_LEFT);
    }
}
