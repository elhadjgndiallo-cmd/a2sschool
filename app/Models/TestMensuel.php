<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestMensuel extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'classe_id',
        'matiere_id',
        'enseignant_id',
        'mois',
        'annee',
        'note',
        'coefficient',
        'created_by'
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'coefficient' => 'integer',
        'mois' => 'integer',
        'annee' => 'integer'
    ];

    /**
     * Relation avec l'élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

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
     * Relation avec l'utilisateur qui a créé le test
     */
    public function createdBy()
    {
        return $this->belongsTo(Utilisateur::class, 'created_by');
    }

    /**
     * Scope pour filtrer par mois et année
     */
    public function scopeParPeriode($query, $mois, $annee)
    {
        return $query->where('mois', $mois)->where('annee', $annee);
    }

    /**
     * Scope pour filtrer par classe
     */
    public function scopeParClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Scope pour filtrer par matière
     */
    public function scopeParMatiere($query, $matiereId)
    {
        return $query->where('matiere_id', $matiereId);
    }

    /**
     * Obtenir le nom du mois en français
     */
    public function getNomMoisAttribute()
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $mois[$this->mois] ?? 'Inconnu';
    }

    /**
     * Obtenir la période complète (mois année)
     */
    public function getPeriodeAttribute()
    {
        return $this->nom_mois . ' ' . $this->annee;
    }

    /**
     * Vérifier si un test mensuel existe déjà pour un élève, matière et période
     */
    public static function existeDeja($eleveId, $matiereId, $mois, $annee)
    {
        return static::where('eleve_id', $eleveId)
                    ->where('matiere_id', $matiereId)
                    ->where('mois', $mois)
                    ->where('annee', $annee)
                    ->exists();
    }

    /**
     * Obtenir les tests mensuels d'une classe pour une période donnée
     */
    public static function getTestsClasse($classeId, $mois, $annee)
    {
        return static::with(['eleve.utilisateur', 'matiere'])
                    ->parClasse($classeId)
                    ->parPeriode($mois, $annee)
                    ->orderBy('eleve_id')
                    ->orderBy('matiere_id')
                    ->get();
    }

    /**
     * Calculer la moyenne mensuelle d'un élève
     */
    public static function calculerMoyenneMensuelle($eleveId, $mois, $annee)
    {
        $tests = static::where('eleve_id', $eleveId)
                      ->parPeriode($mois, $annee)
                      ->get();

        if ($tests->isEmpty()) {
            return null;
        }

        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($tests as $test) {
            $totalPoints += $test->note * $test->coefficient;
            $totalCoefficients += $test->coefficient;
        }

        return $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;
    }
}
