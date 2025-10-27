<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuRappel extends Model
{
    use HasFactory;

    protected $table = 'recus_rappel';

    protected $fillable = [
        'eleve_id',
        'frais_scolarite_id',
        'montant_total_du',
        'montant_paye',
        'montant_restant',
        'montant_a_payer',
        'date_rappel',
        'date_echeance',
        'statut',
        'observations',
        'genere_par',
        'numero_recu_rappel'
    ];

    protected $casts = [
        'montant_total_du' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'montant_a_payer' => 'decimal:2',
        'date_rappel' => 'date',
        'date_echeance' => 'date'
    ];

    /**
     * Relation avec l'élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    /**
     * Relation avec les frais de scolarité
     */
    public function fraisScolarite()
    {
        return $this->belongsTo(FraisScolarite::class);
    }

    /**
     * Relation avec l'utilisateur qui a généré le reçu
     */
    public function generePar()
    {
        return $this->belongsTo(Utilisateur::class, 'genere_par');
    }

    /**
     * Boot method pour générer automatiquement le numéro de reçu
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recuRappel) {
            if (!$recuRappel->numero_recu_rappel) {
                $recuRappel->numero_recu_rappel = static::genererNumeroRecu();
            }
        });
    }

    /**
     * Générer un numéro de reçu unique
     */
    public static function genererNumeroRecu()
    {
        $annee = date('Y');
        $prefixe = "RAP-{$annee}-";
        
        $dernierNumero = static::where('numero_recu_rappel', 'like', $prefixe . '%')
            ->orderBy('numero_recu_rappel', 'desc')
            ->value('numero_recu_rappel');
        
        if ($dernierNumero) {
            $numero = (int) substr($dernierNumero, strlen($prefixe)) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefixe . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope pour les reçus actifs
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour les reçus expirés
     */
    public function scopeExpire($query)
    {
        return $query->where('statut', 'expire');
    }

    /**
     * Scope pour les reçus payés
     */
    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Vérifier si le reçu est expiré
     */
    public function isExpire()
    {
        return $this->date_echeance < now()->toDateString() && $this->statut !== 'paye';
    }

    /**
     * Marquer le reçu comme payé
     */
    public function marquerCommePaye()
    {
        $this->update(['statut' => 'paye']);
    }
}
