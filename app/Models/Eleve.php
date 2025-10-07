<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'classe_id',
        'annee_scolaire_id',
        'numero_etudiant',
        'date_inscription',
        'type_inscription',
        'ecole_origine',
        'statut',
        'situation_matrimoniale',
        'exempte_frais',
        'paiement_annuel',
        'niveau_precedent',
        'etablissement_precedent',
        'observations',
        'documents',
        'actif'
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'documents' => 'array',
        'actif' => 'boolean',
        'exempte_frais' => 'boolean',
        'paiement_annuel' => 'boolean'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    /**
     * Relation avec la classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Relation avec l'année scolaire
     */
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    /**
     * Relation avec les parents
     */
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'parent_eleve', 'eleve_id', 'parent_id')
                    ->withPivot('lien_parente', 'autre_lien_parente', 'responsable_legal', 'autorise_sortie', 'contact_urgence')
                    ->withTimestamps();
    }

    /**
     * Relation avec les notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Relation avec les absences
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Relation avec les frais de scolarité
     */
    public function fraisScolarite()
    {
        return $this->hasMany(FraisScolarite::class);
    }

    /**
     * Relation avec les cartes scolaires
     */
    public function cartesScolaires()
    {
        return $this->hasMany(CarteScolaire::class);
    }

    /**
     * Scope pour les élèves actifs
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
        return $this->utilisateur->nom_complet ?? ($this->utilisateur->prenom . ' ' . $this->utilisateur->nom);
    }
}
