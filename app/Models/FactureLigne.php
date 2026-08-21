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
        if ($this->factureParent()?->estFactureComplement()) {
            return 'Reste à payer';
        }

        $libelle = trim((string) $this->libelle);

        return trim((string) preg_replace('/\s*\((?:reste|partiel)[^)]*\)/u', '', $libelle));
    }

    /**
     * Montant affiché sur la facture (montant BRUT du mois, SANS la remise).
     * La remise s'applique au niveau du total global, pas ligne par ligne.
     */
    public function montantAffiche(): float
    {
        // Toujours afficher le montant brut (tarif du mois complet)
        // La remise est affichée séparément dans le total
        
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

        // Toujours le montant brut, jamais le net
        return (float) $this->montant_brut;
    }

    private function factureParent(): ?Facture
    {
        if ($this->relationLoaded('facture')) {
            return $this->facture;
        }

        return $this->facture()->first(['id', 'facture_origine_id']);
    }
}
