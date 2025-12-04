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
            // Seuils pour primaire (sur 10) selon la nouvelle échelle
            return [
                'tres_bien' => ['min' => 9, 'max' => 10, 'label' => 'Très bien', 'color' => 'success'],
                'bien' => ['min' => 7, 'max' => 8.99, 'label' => 'Bien', 'color' => 'primary'],
                'assez_bien' => ['min' => 6, 'max' => 6.99, 'label' => 'Assez bien', 'color' => 'info'],
                'passable' => ['min' => 5, 'max' => 5.99, 'label' => 'Passable', 'color' => 'warning'],
                'insuffisant' => ['min' => 4, 'max' => 4.99, 'label' => 'Insuffisant', 'color' => 'secondary'],
                'mal' => ['min' => 3, 'max' => 3.99, 'label' => 'Mal', 'color' => 'danger'],
                'mediocre' => ['min' => 0, 'max' => 2.99, 'label' => 'Médiocre', 'color' => 'danger']
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
        if ($note === null) {
            return ['label' => 'Non noté', 'color' => 'secondary'];
        }
        
        $seuils = $this->seuils_appreciation;
        
        // Vérifier dans l'ordre décroissant pour les primaires (du plus haut au plus bas)
        if ($this->isPrimaire()) {
            // Pour primaire, vérifier du plus haut au plus bas
            $order = ['tres_bien', 'bien', 'assez_bien', 'passable', 'insuffisant', 'mal', 'mediocre'];
            foreach ($order as $key) {
                if (isset($seuils[$key]) && $note >= $seuils[$key]['min'] && $note <= $seuils[$key]['max']) {
                    return $seuils[$key];
                }
            }
        } else {
            // Pour secondaire, vérifier normalement
            foreach ($seuils as $key => $seuil) {
                if ($note >= $seuil['min'] && $note <= $seuil['max']) {
                    return $seuil;
                }
            }
        }
        
        // Par défaut, retourner le dernier seuil (le plus bas)
        return end($seuils);
    }

    /**
     * Mettre à jour l'effectif actuel de la classe
     */
    public function updateEffectifActuel()
    {
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        if ($anneeScolaireActive) {
            $effectif = $this->eleves()->where('annee_scolaire_id', $anneeScolaireActive->id)->count();
            $this->update(['effectif_actuel' => $effectif]);
        }
    }
}
