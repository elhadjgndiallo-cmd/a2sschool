<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'matiere_id',
        'date_absence',
        'heure_debut',
        'heure_fin',
        'type',
        'statut',
        'motif',
        'document_justificatif',
        'saisi_par',
        'notifie_parents_at'
    ];

    protected $casts = [
        'date_absence' => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'notifie_parents_at' => 'datetime'
    ];

    /**
     * Relation avec l'élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    /**
     * Relation avec la matière
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    /**
     * Relation avec l'utilisateur qui a saisi
     */
    public function saisiPar()
    {
        return $this->belongsTo(Utilisateur::class, 'saisi_par');
    }

    /**
     * Scope pour les absences non justifiées
     */
    public function scopeNonJustifiees($query)
    {
        return $query->where('statut', 'non_justifiee');
    }
}
