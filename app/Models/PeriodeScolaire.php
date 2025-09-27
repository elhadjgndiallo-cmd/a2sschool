<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeScolaire extends Model
{
    use HasFactory;

    protected $table = 'periodes_scolaires';

    protected $fillable = [
        'nom',
        'date_debut',
        'date_fin',
        'date_conseil',
        'couleur',
        'actif',
        'ordre'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_conseil' => 'date',
        'actif' => 'boolean',
    ];

    /**
     * Scope pour les périodes actives
     */
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour ordonner par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Vérifier si une date est dans cette période
     */
    public function contientDate($date)
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $date->between($this->date_debut, $this->date_fin);
    }

    /**
     * Obtenir la période actuelle basée sur la date
     */
    public static function getPeriodeActuelle()
    {
        $aujourdhui = now();
        
        return static::actives()
            ->where('date_debut', '<=', $aujourdhui)
            ->where('date_fin', '>=', $aujourdhui)
            ->first();
    }

    /**
     * Obtenir toutes les périodes actives ordonnées
     */
    public static function getPeriodesActives()
    {
        return static::actives()->ordered()->get();
    }

    /**
     * Obtenir la couleur Bootstrap correspondante
     */
    public function getCouleurBootstrapAttribute()
    {
        $couleurs = [
            'primary' => 'primary',
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            'info' => 'info',
            'secondary' => 'secondary',
        ];

        return $couleurs[$this->couleur] ?? 'primary';
    }

    /**
     * Obtenir le nom formaté avec les dates
     */
    public function getNomCompletAttribute()
    {
        return "{$this->nom} ({$this->date_debut->format('d/m/Y')} - {$this->date_fin->format('d/m/Y')})";
    }
}