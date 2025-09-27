<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'numero_employe',
        'date_embauche',
        'specialite',
        'statut',
        'salaire',
        'qualifications',
        'actif'
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire' => 'decimal:2',
        'actif' => 'boolean'
    ];

    /**
     * Relations
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function emploisTemps()
    {
        return $this->hasMany(EmploiTemps::class);
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere');
    }

    public function cartesEnseignants()
    {
        return $this->hasMany(CarteEnseignant::class);
    }

    /**
     * Scope pour les enseignants actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return $this->utilisateur->name;
    }
}
