<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'utilisateur_id',
        'profession',
        'employeur',
        'telephone_travail',
        'lien_parente',
        'contact_urgence',
        'actif'
    ];

    protected $casts = [
        'contact_urgence' => 'boolean',
        'actif' => 'boolean'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    /**
     * Relation avec les élèves
     */
    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'parent_eleve', 'parent_id', 'eleve_id')
                    ->withPivot('lien_parente', 'autre_lien_parente', 'responsable_legal', 'autorise_sortie', 'contact_urgence')
                    ->withTimestamps();
    }

    /**
     * Scope pour les parents actifs
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
