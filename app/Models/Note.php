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
        'coefficient' => 'float',
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
        $classe = $this->eleve->classe ?? null;
        $isPrimaire = $classe ? $classe->isPrimaire() : false;

        // Primaire / préscolaire : uniquement la composition
        if ($isPrimaire) {
            return $this->note_composition !== null ? round((float) $this->note_composition, 2) : null;
        }

        // Collège / lycée : cours seul, composition seule, ou les deux
        if ($this->note_cours === null && $this->note_composition === null) {
            return null;
        }

        if ($this->note_cours === null) {
            return round((float) $this->note_composition, 2);
        }

        if ($this->note_composition === null) {
            return round((float) $this->note_cours, 2);
        }

        $noteFinale = ((float) $this->note_cours + ((float) $this->note_composition * 2)) / 3;

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
     *
     * IMPORTANT :
     * - Utilise le coefficient de chaque NOTE (modifiable par l'enseignant).
     * - Si une note n'a pas de coefficient, on utilise celui de la matière.
     * - Formule : pour chaque matière, moyenne = sum(note_finale × coef_note) / sum(coef_note)
     *   puis moyenne générale = sum(moyenne_matiere × coef_matiere) / sum(coef_matiere)
     *   où coef_matiere = sum(coefficients des notes de cette matière).
     */
    public static function calculerMoyenneGenerale($eleveId, $periode)
    {
        $notes = self::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');

        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere ?? null;
            $coefMatiereDefaut = $matiere ? ($matiere->coefficient ?? 1) : 1;

            // Moyenne pondérée par le coefficient de chaque note
            $sommeNoteCoeff = 0;
            $sommeCoeff = 0;

            foreach ($notesMatiere as $note) {
                $noteFinale = $note->note_finale ?? $note->calculerNoteFinale();
                if ($noteFinale === null) {
                    continue;
                }
                $coefNote = $note->coefficient ?? $coefMatiereDefaut;
                if ($coefNote <= 0) {
                    $coefNote = $coefMatiereDefaut;
                }
                $sommeNoteCoeff += $noteFinale * $coefNote;
                $sommeCoeff += $coefNote;
            }

            if ($sommeCoeff <= 0) {
                continue;
            }

            $moyenneMatiere = $sommeNoteCoeff / $sommeCoeff;
            $totalPoints += $moyenneMatiere * $sommeCoeff;
            $totalCoefficients += $sommeCoeff;
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
