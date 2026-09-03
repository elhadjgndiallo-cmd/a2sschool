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

    /**
     * Matières liées à l'année scolaire (EDT, notes ou enseignants de l'année).
     */
    public function scopePourAnneeScolaire($query, ?int $anneeScolaireId)
    {
        if (!$anneeScolaireId) {
            return $query;
        }

        return $query->where(function ($q) use ($anneeScolaireId) {
            $q->whereHas('emploisTemps', function ($et) use ($anneeScolaireId) {
                $et->where('actif', true)
                    ->where('annee_scolaire_id', $anneeScolaireId);
            })->orWhereHas('enseignants', function ($ens) use ($anneeScolaireId) {
                $ens->where('annee_scolaire_id', $anneeScolaireId);
            })->orWhereHas('notes.eleve', function ($e) use ($anneeScolaireId) {
                $e->where('annee_scolaire_id', $anneeScolaireId);
            });
        });
    }

    /**
     * Matières de l'année scolaire active.
     */
    public function scopePourAnneeActive($query)
    {
        $annee = AnneeScolaire::anneeActive();

        if (!$annee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->pourAnneeScolaire($annee->id);
    }

    /**
     * Liste triée pour les listes déroulantes (matières actives).
     * Le catalogue n'est pas filtré par année : une matière fraîchement créée
     * n'a pas encore d'EDT/notes et doit quand même pouvoir être choisie.
     */
    public static function listeDeroulante(?int $anneeScolaireId = null): \Illuminate\Support\Collection
    {
        return static::query()->actif()->orderBy('nom')->get();
    }
}
