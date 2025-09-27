<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploiTemps extends Model
{
    use HasFactory;

    protected $table = 'emplois_temps';

    protected $fillable = [
        'classe_id',
        'matiere_id',
        'enseignant_id',
        'jour_semaine',
        'heure_debut',
        'heure_fin',
        'salle',
        'type_cours',
        'date_debut',
        'date_fin',
        'actif'
    ];

    protected $casts = [
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean'
    ];

    /**
     * Relation avec la classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Relation avec la matière
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    /**
     * Relation avec l'enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Scope pour les emplois du temps actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour filtrer par jour
     */
    public function scopeJour($query, $jour)
    {
        return $query->where('jour_semaine', $jour);
    }

    /**
     * Vérifier s'il y a des conflits d'horaires pour un enseignant
     */
    public static function verifierConflitsEnseignant($enseignantId, $jour, $heureDebut, $heureFin, $excludeId = null)
    {
        $query = static::where('enseignant_id', $enseignantId)
            ->where('jour_semaine', $jour)
            ->where('actif', true)
            ->where(function($q) use ($heureDebut, $heureFin) {
                $q->where(function($subQ) use ($heureDebut, $heureFin) {
                    // Le nouveau créneau commence pendant un créneau existant
                    $subQ->where('heure_debut', '<=', $heureDebut)
                         ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function($subQ) use ($heureDebut, $heureFin) {
                    // Le nouveau créneau se termine pendant un créneau existant
                    $subQ->where('heure_debut', '<', $heureFin)
                         ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function($subQ) use ($heureDebut, $heureFin) {
                    // Le nouveau créneau englobe complètement un créneau existant
                    $subQ->where('heure_debut', '>=', $heureDebut)
                         ->where('heure_fin', '<=', $heureFin);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->with(['classe', 'matiere'])->get();
    }

    /**
     * Vérifier s'il y a des conflits d'horaires pour une classe
     */
    public static function verifierConflitsClasse($classeId, $jour, $heureDebut, $heureFin, $excludeId = null)
    {
        $query = static::where('classe_id', $classeId)
            ->where('jour_semaine', $jour)
            ->where('actif', true)
            ->where(function($q) use ($heureDebut, $heureFin) {
                $q->where(function($subQ) use ($heureDebut, $heureFin) {
                    $subQ->where('heure_debut', '<=', $heureDebut)
                         ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function($subQ) use ($heureDebut, $heureFin) {
                    $subQ->where('heure_debut', '<', $heureFin)
                         ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function($subQ) use ($heureDebut, $heureFin) {
                    $subQ->where('heure_debut', '>=', $heureDebut)
                         ->where('heure_fin', '<=', $heureFin);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->with(['enseignant.utilisateur', 'matiere'])->get();
    }
}
