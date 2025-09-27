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
}
