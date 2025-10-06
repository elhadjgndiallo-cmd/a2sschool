<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'niveau',
        'section',
        'effectif_max',
        'effectif_actuel',
        'description',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'effectif_max' => 'integer',
        'effectif_actuel' => 'integer'
    ];

    /**
     * Relation avec les élèves
     */
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    /**
     * Relation avec les emplois du temps
     */
    public function emploisTemps()
    {
        return $this->hasMany(EmploiTemps::class);
    }

    /**
     * Relation avec les matières (via emplois du temps)
     */
    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'emplois_temps', 'classe_id', 'matiere_id')
            ->where('matieres.actif', '=', 1)
            ->distinct();
    }

    /**
     * Scope pour les classes actives
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Accessor pour le nom complet de la classe
     */
    public function getNomCompletAttribute()
    {
        return $this->niveau . ($this->section ? ' ' . $this->section : '');
    }

    /**
     * Déterminer si la classe est primaire
     */
    public function isPrimaire()
    {
        return strtolower($this->niveau) === 'primaire';
    }

    /**
     * Déterminer si la classe est secondaire
     */
    public function isSecondaire()
    {
        return in_array(strtolower($this->niveau), ['lycée', 'college', 'secondaire']);
    }

    /**
     * Obtenir la barème de notation selon le niveau
     */
    public function getNoteMaxAttribute()
    {
        return $this->isPrimaire() ? 10 : 20;
    }

    /**
     * Obtenir le seuil de réussite selon le niveau
     */
    public function getSeuilReussiteAttribute()
    {
        return $this->isPrimaire() ? 5 : 10;
    }

    /**
     * Obtenir les seuils d'appréciation selon le niveau
     */
    public function getSeuilsAppreciationAttribute()
    {
        if ($this->isPrimaire()) {
            // Seuils pour primaire (sur 10)
            return [
                'excellent' => ['min' => 9, 'max' => 10, 'label' => 'Excellent', 'color' => 'success'],
                'tres_bien' => ['min' => 8, 'max' => 8.99, 'label' => 'Très bien', 'color' => 'primary'],
                'bien' => ['min' => 7, 'max' => 7.99, 'label' => 'Bien', 'color' => 'info'],
                'assez_bien' => ['min' => 6, 'max' => 6.99, 'label' => 'Assez bien', 'color' => 'warning'],
                'passable' => ['min' => 5, 'max' => 5.99, 'label' => 'Passable', 'color' => 'secondary'],
                'insuffisant' => ['min' => 0, 'max' => 4.99, 'label' => 'Insuffisant', 'color' => 'danger']
            ];
        } else {
            // Seuils pour secondaire (sur 20)
            return [
                'excellent' => ['min' => 18, 'max' => 20, 'label' => 'Excellent', 'color' => 'success'],
                'tres_bien' => ['min' => 16, 'max' => 17.99, 'label' => 'Très bien', 'color' => 'primary'],
                'bien' => ['min' => 14, 'max' => 15.99, 'label' => 'Bien', 'color' => 'info'],
                'assez_bien' => ['min' => 12, 'max' => 13.99, 'label' => 'Assez bien', 'color' => 'warning'],
                'passable' => ['min' => 10, 'max' => 11.99, 'label' => 'Passable', 'color' => 'secondary'],
                'insuffisant' => ['min' => 0, 'max' => 9.99, 'label' => 'Insuffisant', 'color' => 'danger']
            ];
        }
    }

    /**
     * Obtenir l'appréciation d'une note selon le niveau
     */
    public function getAppreciation($note)
    {
        $seuils = $this->seuils_appreciation;
        
        foreach ($seuils as $key => $seuil) {
            if ($note >= $seuil['min'] && $note <= $seuil['max']) {
                return $seuil;
            }
        }
        
        return $seuils['insuffisant']; // Par défaut
    }
}
