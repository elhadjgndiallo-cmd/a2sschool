<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'date_debut',
        'date_fin',
        'active',
        'description'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'active' => 'boolean'
    ];

    /**
     * Scope pour l'année scolaire active
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Obtenir l'année scolaire active
     */
    public static function anneeActive()
    {
        return self::active()->first();
    }

    /**
     * Activer cette année scolaire (et désactiver les autres)
     */
    public function activer()
    {
        // Désactiver toutes les autres années
        self::where('id', '!=', $this->id)->update(['active' => false]);
        
        // Activer cette année
        $this->update(['active' => true]);
    }

    /**
     * Vérifier si l'année est en cours (basé sur les dates)
     */
    public function estEnCours()
    {
        $maintenant = now()->toDateString();
        return $maintenant >= $this->date_debut && $maintenant <= $this->date_fin;
    }

    /**
     * Obtenir le statut de l'année
     */
    public function getStatutAttribute()
    {
        $maintenant = now()->toDateString();
        
        if ($maintenant < $this->date_debut) {
            return 'à_venir';
        } elseif ($maintenant > $this->date_fin) {
            return 'terminee';
        } else {
            return 'en_cours';
        }
    }
}
