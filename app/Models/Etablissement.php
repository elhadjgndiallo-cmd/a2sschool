<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'email',
        'slogan',
        'logo',
        'cachet',
        'description',
        'dg',
        'directeur_primaire',
        'prefixe_matricule',
        'suffixe_matricule',
        'statut_etablissement',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean'
    ];

    /**
     * Scope pour les établissements actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Obtenir l'établissement principal (le premier actif)
     */
    public static function principal()
    {
        return self::actif()->first();
    }

    /**
     * Générer le prochain numéro matricule
     */
    public function genererNumeroMatricule()
    {
        if (!$this->prefixe_matricule) {
            return null;
        }

        // Compter le nombre d'élèves existants avec ce préfixe
        $count = \App\Models\Eleve::where('numero_etudiant', 'like', $this->prefixe_matricule . '%')
                                 ->count();
        
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        return $this->prefixe_matricule . $nextNumber . ($this->suffixe_matricule ?? '');
    }
}
