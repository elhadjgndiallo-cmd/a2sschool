<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactureLigne extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'type_frais',
        'mois',
        'libelle',
        'montant_brut',
        'montant_remise',
        'montant_net',
        'tranche_paiement_id',
        'frais_scolarite_id',
        'paiement_id',
    ];

    protected $casts = [
        'mois' => 'date',
        'montant_brut' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'montant_net' => 'decimal:2',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function tranchePaiement(): BelongsTo
    {
        return $this->belongsTo(TranchePaiement::class);
    }

    public function fraisScolarite(): BelongsTo
    {
        return $this->belongsTo(FraisScolarite::class);
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    /**
     * Libellé affiché sur la facture (sans mention partiel / reste).
     */
    public function libelleAffiche(): string
    {
        $libelle = trim((string) $this->libelle);

        return trim((string) preg_replace('/\s*\((?:reste|partiel)[^)]*\)/u', '', $libelle));
    }

    /**
     * Montant mensuel / unitaire affiché sur la facture (remise uniquement au total brut).
     */
    public function montantAffiche(): float
    {
        if ($this->tranche_paiement_id) {
            $tranche = $this->relationLoaded('tranchePaiement')
                ? $this->tranchePaiement
                : $this->tranchePaiement()->first();

            if ($tranche) {
                return (float) $tranche->montant_tranche;
            }
        }

        if ($this->frais_scolarite_id) {
            $frais = $this->relationLoaded('fraisScolarite')
                ? $this->fraisScolarite
                : $this->fraisScolarite()->first();

            if ($frais) {
                return (float) $frais->montant;
            }
        }

        return (float) $this->montant_brut;
    }
}
