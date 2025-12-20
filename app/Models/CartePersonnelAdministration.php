<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartePersonnelAdministration extends Model
{
    use HasFactory;

    protected $table = 'cartes_personnel_administration';

    protected $fillable = [
        'personnel_administration_id',
        'numero_carte',
        'date_emission',
        'date_expiration',
        'statut',
        'type_carte',
        'photo_path',
        'qr_code',
        'observations',
        'emise_par',
        'validee_par'
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_expiration' => 'date'
    ];

    /**
     * Relation avec le personnel d'administration
     */
    public function personnelAdministration()
    {
        return $this->belongsTo(PersonnelAdministration::class);
    }

    /**
     * Relation avec l'utilisateur qui a émis la carte
     */
    public function emisePar()
    {
        return $this->belongsTo(Utilisateur::class, 'emise_par');
    }

    /**
     * Relation avec l'utilisateur qui a validé la carte
     */
    public function valideePar()
    {
        return $this->belongsTo(Utilisateur::class, 'validee_par');
    }

    /**
     * Scope pour les cartes actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'active');
    }

    /**
     * Scope pour les cartes expirées
     */
    public function scopeExpiree($query)
    {
        return $query->where('date_expiration', '<', now());
    }

    /**
     * Scope pour les cartes valides
     */
    public function scopeValide($query)
    {
        return $query->where('statut', 'active')
                    ->where('date_expiration', '>=', now());
    }

    /**
     * Accessor pour vérifier si la carte est valide
     */
    public function getEstValideAttribute()
    {
        return $this->statut === 'active' && $this->date_expiration >= now();
    }

    /**
     * Accessor pour le statut formaté
     */
    public function getStatutLibelleAttribute()
    {
        $statuts = [
            'active' => 'Active',
            'expiree' => 'Expirée',
            'suspendue' => 'Suspendue',
            'annulee' => 'Annulée'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Accessor pour le type de carte formaté
     */
    public function getTypeCarteLibelleAttribute()
    {
        $types = [
            'standard' => 'Standard',
            'temporaire' => 'Temporaire',
            'remplacement' => 'Remplacement'
        ];

        return $types[$this->type_carte] ?? $this->type_carte;
    }

    /**
     * Générer un numéro de carte unique
     */
    public static function genererNumeroCarte()
    {
        do {
            $numero = 'ADM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('numero_carte', $numero)->exists());

        return $numero;
    }
}
