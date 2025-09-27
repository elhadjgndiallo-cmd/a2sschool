<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entree extends Model
{
    protected $fillable = [
        'libelle',
        'description',
        'montant',
        'date_entree',
        'source',
        'mode_paiement',
        'reference',
        'enregistre_par'
    ];

    protected $casts = [
        'date_entree' => 'date',
        'montant' => 'decimal:2'
    ];

    /**
     * Relation avec l'utilisateur qui a enregistré l'entrée
     */
    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'enregistre_par');
    }

    /**
     * Accessor pour formater le montant en GNF
     */
    public function getMontantFormateAttribute(): string
    {
        return number_format($this->montant, 0, ',', ' ') . ' GNF';
    }
}
