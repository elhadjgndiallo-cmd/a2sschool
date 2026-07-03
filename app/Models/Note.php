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
            ->get();

        return self::calculerMoyenneGeneraleDepuisCollection($notes);
    }

    /**
     * Moyenne générale trimestrielle à partir d'une collection de notes (groupée par matière).
     */
    public static function calculerMoyenneGeneraleDepuisCollection($notes): float
    {
        $grouped = collect($notes)->groupBy('matiere_id');
        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($grouped as $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere ?? null;
            $coefMatiereDefaut = $matiere ? ($matiere->coefficient ?? 1) : 1;

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
     * Moyenne finale d'une matière pour un trimestre (agrège plusieurs lignes si besoin).
     */
    public static function moyenneFinaleMatierePeriode($notesCollection, int $matiereId): ?float
    {
        $notesMatiere = collect($notesCollection)->where('matiere_id', $matiereId);
        if ($notesMatiere->isEmpty()) {
            return null;
        }

        $matiere = $notesMatiere->first()->matiere ?? null;
        $coefMatiereDefaut = $matiere ? ($matiere->coefficient ?? 1) : 1;

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

        return $sommeCoeff > 0 ? round($sommeNoteCoeff / $sommeCoeff, 2) : null;
    }

    /**
     * Moyenne annuelle d'une matière : moyenne arithmétique des notes finales par trimestre.
     */
    public static function calculerMoyenneAnnuelleMatiere(array $notesFinalesParTrimestre): float
    {
        $notesValides = array_values(array_filter($notesFinalesParTrimestre, fn ($n) => $n !== null));

        if (empty($notesValides)) {
            return 0;
        }

        return round(array_sum($notesValides) / count($notesValides), 2);
    }

    /**
     * Moyenne générale annuelle : moyenne simple des moyennes trimestrielles.
     */
    public static function calculerMoyenneGeneraleAnnuelle(array $moyennesParTrimestre): float
    {
        $moyennesValides = array_values(array_filter($moyennesParTrimestre, fn ($m) => $m !== null));

        if (empty($moyennesValides)) {
            return 0;
        }

        return round(array_sum($moyennesValides) / count($moyennesValides), 2);
    }

    /**
     * Notes finales agrégées par matière et par trimestre.
     */
    public static function construireNotesFinalesParMatiereParPeriode(array $notesParPeriode, array $periodes): array
    {
        $matiereIds = [];
        foreach ($periodes as $periode) {
            if (!isset($notesParPeriode[$periode])) {
                continue;
            }
            foreach ($notesParPeriode[$periode] as $note) {
                $matiereIds[$note->matiere_id] = true;
            }
        }

        $result = [];
        foreach (array_keys($matiereIds) as $matiereId) {
            $result[$matiereId] = [];
            foreach ($periodes as $periode) {
                if (!isset($notesParPeriode[$periode])) {
                    continue;
                }
                $moyenne = self::moyenneFinaleMatierePeriode($notesParPeriode[$periode], $matiereId);
                if ($moyenne !== null) {
                    $result[$matiereId][$periode] = $moyenne;
                }
            }
        }

        return $result;
    }

    /**
     * Moyennes annuelles par matière à partir des notes par période.
     */
    public static function construireMoyennesAnnuellesParMatiere(array $notesParPeriode, array $periodes): array
    {
        $notesFinales = self::construireNotesFinalesParMatiereParPeriode($notesParPeriode, $periodes);
        $result = [];

        foreach ($notesFinales as $matiereId => $notesParTrimestre) {
            $result[$matiereId] = self::calculerMoyenneAnnuelleMatiere(array_values($notesParTrimestre));
        }

        return $result;
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
