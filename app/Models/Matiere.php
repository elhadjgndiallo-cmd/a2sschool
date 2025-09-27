<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'coefficient',
        'couleur',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'coefficient' => 'integer'
    ];

    /**
     * Relation avec les notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Relation avec les emplois du temps
     */
    public function emploisTemps()
    {
        return $this->hasMany(EmploiTemps::class);
    }

    /**
     * Relation avec les absences
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function enseignants()
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere');
    }

    /**
     * Scope pour les matières actives
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
