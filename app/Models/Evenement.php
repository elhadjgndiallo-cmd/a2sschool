<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'evenements';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titre',
        'description',
        'lieu',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'journee_entiere',
        'type',
        'couleur',
        'public',
        'classe_id',
        'createur_id',
        'rappel',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'journee_entiere' => 'boolean',
        'public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtenir la classe associée à cet événement.
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Obtenir le créateur de l'événement.
     */
    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'createur_id');
    }

    /**
     * Scope pour filtrer les événements publics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    /**
     * Scope pour filtrer les événements par type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer les événements à venir.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAVenir($query)
    {
        return $query->where('date_debut', '>=', now()->startOfDay());
    }

    /**
     * Scope pour filtrer les événements passés.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePasses($query)
    {
        return $query->where('date_fin', '<', now()->startOfDay());
    }

    /**
     * Scope pour filtrer les événements en cours.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnCours($query)
    {
        $today = now()->startOfDay();
        return $query->where('date_debut', '<=', $today)
                     ->where('date_fin', '>=', $today);
    }

    /**
     * Scope pour filtrer les événements par période (mois/année).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $mois
     * @param  int  $annee
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePeriode($query, $mois, $annee)
    {
        return $query->whereRaw("(MONTH(date_debut) = ? AND YEAR(date_debut) = ?) OR "
            . "(MONTH(date_fin) = ? AND YEAR(date_fin) = ?) OR "
            . "(date_debut <= LAST_DAY(?) AND date_fin >= ?)", 
            [$mois, $annee, $mois, $annee, "$annee-$mois-01", "$annee-$mois-01"]);
    }

    /**
     * Scope pour filtrer les événements par classe.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $classeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePourClasse($query, $classeId)
    {
        return $query->where(function($q) use ($classeId) {
            $q->where('classe_id', $classeId)
              ->orWhere('public', true);
        });
    }

    /**
     * Vérifier si l'événement est à venir.
     *
     * @return bool
     */
    public function estAVenir()
    {
        return $this->date_debut >= now()->startOfDay();
    }

    /**
     * Vérifier si l'événement est passé.
     *
     * @return bool
     */
    public function estPasse()
    {
        return $this->date_fin < now()->startOfDay();
    }

    /**
     * Vérifier si l'événement est en cours.
     *
     * @return bool
     */
    public function estEnCours()
    {
        $today = now()->startOfDay();
        return $this->date_debut <= $today && $this->date_fin >= $today;
    }
}