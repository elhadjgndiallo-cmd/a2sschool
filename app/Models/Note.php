<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'matiere_id',
        'enseignant_id',
        'note_cours',
        'note_composition',
        'note_finale',
        'note_sur',
        'type_evaluation',
        'titre',
        'commentaire',
        'date_evaluation',
        'periode',
        'coefficient',
        'rattrapage'
    ];

    protected $casts = [
        'note_cours' => 'decimal:2',
        'note_composition' => 'decimal:2',
        'note_finale' => 'decimal:2',
        'note_sur' => 'decimal:2',
        'date_evaluation' => 'date',
        'coefficient' => 'integer',
        'rattrapage' => 'boolean'
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
     * Relation avec l'enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Accessor pour la note sur 20 (compatibilité)
     */
    public function getNoteSur20Attribute()
    {
        return $this->note_finale ?? 0;
    }

    /**
     * Calculer la note finale selon la formule
     * Pour primaire : note finale = note composition
     * Pour collège/lycée : Note finale = (NOTE DE COURS + NOTES DE COMPO * 2) / 3
     */
    public function calculerNoteFinale()
    {
        if ($this->note_composition === null) {
            return null;
        }

        // Récupérer la classe pour déterminer le niveau
        $classe = $this->eleve->classe ?? null;
        $isPrimaire = $classe ? $classe->isPrimaire() : false;

        // Pour primaire : note finale = note composition
        if ($isPrimaire) {
            return $this->note_composition;
        }

        // Pour collège/lycée : (Note Cours + (Note Composition * 2)) / 3
        $noteCours = $this->note_cours ?? 0;
        $noteComposition = $this->note_composition ?? 0;
        
        // Si une seule note est présente, utiliser celle-ci
        if ($this->note_cours === null) {
            $noteFinale = $noteComposition;
        } elseif ($this->note_composition === null) {
            $noteFinale = $noteCours;
        } else {
            // Appliquer la formule : (NOTE DE COURS + NOTES DE COMPO * 2) / 3
            $noteFinale = ($noteCours + ($noteComposition * 2)) / 3;
        }

        return round($noteFinale, 2);
    }

    /**
     * Accessor pour obtenir la note finale calculée
     */
    public function getNoteFinaleCalculeeAttribute()
    {
        return $this->calculerNoteFinale();
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopePeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Calculer la moyenne générale d'un élève pour un trimestre
     * Moyenne générale = somme de tous les Notes finales / somme de tous les coefficients
     */
    public static function calculerMoyenneGenerale($eleveId, $periode)
    {
        $notes = self::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with('matiere')
            ->get();

        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($notes as $note) {
            $noteFinale = $note->calculerNoteFinale();
            if ($noteFinale !== null) {
                $coefMatiere = $note->matiere ? ($note->matiere->coefficient ?? 1) : 1;
                $totalPoints += $noteFinale * $coefMatiere;
                $totalCoefficients += $coefMatiere;
            }
        }

        return $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;
    }

    /**
     * Boot method pour calculer automatiquement la note finale
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($note) {
            if ($note->note_cours !== null || $note->note_composition !== null) {
                $note->note_finale = $note->calculerNoteFinale();
            }
        });
    }
}
