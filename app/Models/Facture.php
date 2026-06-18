<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_facture',
        'eleve_id',
        'annee_scolaire_id',
        'date_facture',
        'date_echeance',
        'sous_total',
        'remise_type',
        'remise_valeur',
        'montant_remise',
        'total',
        'mode_paiement',
        'reference_paiement',
        'observations',
        'statut',
        'genere_par',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'sous_total' => 'decimal:2',
        'remise_valeur' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Facture $facture) {
            if (!$facture->numero_facture) {
                $facture->numero_facture = static::genererNumeroFacture();
            }
        });
    }

    public static function genererNumeroFacture(): string
    {
        $annee = date('Y');
        $prefixe = "FAC-{$annee}-";

        $dernier = static::where('numero_facture', 'like', $prefixe . '%')
            ->orderByDesc('numero_facture')
            ->value('numero_facture');

        $numero = $dernier
            ? (int) substr($dernier, strlen($prefixe)) + 1
            : 1;

        return $prefixe . str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
    }

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function generePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'genere_par');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class);
    }
}
