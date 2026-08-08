<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonSalaireEnseignant extends Model
{
    use HasFactory;

    protected $table = 'bons_salaire_enseignants';

    protected $fillable = [
        'enseignant_id',
        'annee_scolaire_id',
        'numero_bon',
        'montant',
        'date_bon',
        'mois_reference',
        'statut',
        'salaire_enseignant_id',
        'deduit_le',
        'mode_paiement',
        'reference_paiement',
        'observations',
        'cree_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_bon' => 'date',
        'mois_reference' => 'date',
        'deduit_le' => 'date',
    ];

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function salaireEnseignant(): BelongsTo
    {
        return $this->belongsTo(SalaireEnseignant::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par');
    }

    public function depense()
    {
        return $this->hasOne(Depense::class, 'bon_salaire_enseignant_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopePourEnseignant($query, int $enseignantId)
    {
        return $query->where('enseignant_id', $enseignantId);
    }

    public static function genererNumeroBon(): string
    {
        $annee = now()->format('Y');
        $dernier = static::where('numero_bon', 'like', "BON-SAL-{$annee}-%")
            ->orderByDesc('id')
            ->value('numero_bon');

        $sequence = 1;
        if ($dernier && preg_match('/-(\d+)$/', $dernier, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('BON-SAL-%s-%05d', $annee, $sequence);
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'actif' => 'Actif',
            'deduit' => 'Déduit',
            'annule' => 'Annulé',
            default => ucfirst($this->statut),
        };
    }
}
