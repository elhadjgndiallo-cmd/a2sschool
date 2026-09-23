<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceEnseignant extends Model
{
    use HasFactory;

    protected $table = 'absences_enseignants';

    protected $fillable = [
        'enseignant_id',
        'classe_id',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'type',
        'motif_categorie',
        'motif',
        'document_justificatif',
        'origine',
        'statut',
        'saisi_par',
        'traite_par',
        'traite_at',
        'commentaire_admin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'traite_at' => 'datetime',
    ];

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function saisiPar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'saisi_par');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'traite_par');
    }

    public function scopeQuiChevauche($query, int $enseignantId, $debut, $fin, ?int $exceptId = null)
    {
        return $query->where('enseignant_id', $enseignantId)
            ->where('statut', '!=', 'refusee')
            ->whereDate('date_debut', '<=', $fin)
            ->whereDate('date_fin', '>=', $debut)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId));
    }

    public function scopeQuiChevaucheCreneau($query, int $enseignantId, $date, $heureDebut, $heureFin, ?int $exceptId = null)
    {
        return $query->where('enseignant_id', $enseignantId)
            ->where('statut', '!=', 'refusee')
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date)
            ->where(function ($q) use ($heureDebut, $heureFin) {
                $q->whereNull('heure_debut')
                    ->orWhereNull('heure_fin')
                    ->orWhere(function ($qq) use ($heureDebut, $heureFin) {
                        $qq->where('heure_debut', '<', $heureFin)
                            ->where('heure_fin', '>', $heureDebut);
                    });
            })
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId));
    }

    public function dureeMinutes(): int
    {
        $debut = $this->toMinutes($this->heure_debut);
        $fin = $this->toMinutes($this->heure_fin);

        if ($debut === null || $fin === null || $fin <= $debut) {
            return 0;
        }

        return $fin - $debut;
    }

    public static function formatDureeMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0h';
        }

        $heures = intdiv($minutes, 60);
        $reste = $minutes % 60;

        if ($reste === 0) {
            return $heures . 'h';
        }

        if ($heures === 0) {
            return $reste . ' min';
        }

        return $heures . 'h' . str_pad((string) $reste, 2, '0', STR_PAD_LEFT);
    }

    public function getHeuresLabelAttribute(): string
    {
        $debut = $this->formatHeure($this->heure_debut);
        $fin = $this->formatHeure($this->heure_fin);

        if (!$debut && !$fin) {
            return 'Journée';
        }

        return ($debut ?: '—') . '-' . ($fin ?: '—');
    }

    public function getCreneauLabelAttribute(): string
    {
        $classe = $this->classe?->nom;
        $label = $this->heures_label;

        return $classe ? $label . ' · ' . $classe : $label;
    }

    private function toMinutes($value): ?int
    {
        if (!$value) {
            return null;
        }

        $texte = is_object($value) && method_exists($value, 'format')
            ? $value->format('H:i')
            : substr((string) $value, 0, 5);

        if (!preg_match('/^(\d{1,2}):(\d{2})/', $texte, $matches)) {
            return null;
        }

        return ((int) $matches[1]) * 60 + (int) $matches[2];
    }

    private function formatHeure($value): ?string
    {
        if (!$value) {
            return null;
        }

        $texte = is_object($value) && method_exists($value, 'format')
            ? $value->format('H:i')
            : substr((string) $value, 0, 5);

        return str_replace(':', 'h', $texte);
    }

    public function scopeCouvrantLaDate($query, $date)
    {
        return $query->where('statut', '!=', 'refusee')
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date);
    }

    public function scopeDuMois($query, $debut, $fin)
    {
        return $query->where('statut', '!=', 'refusee')
            ->whereDate('date_debut', '<=', $fin)
            ->whereDate('date_fin', '>=', $debut);
    }

    public function getJourChomeLabelAttribute(): string
    {
        $date = $this->date_debut;

        if (!$date) {
            return '—';
        }

        return ucfirst($date->locale('fr')->isoFormat('dddd D MMMM YYYY'));
    }

    public function peutEtreJustifiee(): bool
    {
        return in_array($this->statut, ['non_justifiee', 'en_attente'], true);
    }

    public function peutEtreTraitee(): bool
    {
        return $this->statut === 'en_attente' && $this->origine === 'enseignant';
    }

    public function peutEtreAnnulee(): bool
    {
        return $this->statut === 'en_attente' && $this->origine === 'enseignant';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'retard' => 'Retard',
            'conge' => 'Congé',
            default => 'Absence',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'justifiee' => 'Justifiée',
            'en_attente' => 'En attente',
            'refusee' => 'Refusée',
            default => 'Non justifiée',
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'justifiee' => 'success',
            'en_attente' => 'warning',
            'refusee' => 'danger',
            default => 'secondary',
        };
    }

    public function getOrigineLabelAttribute(): string
    {
        return $this->origine === 'enseignant' ? 'Déclaration enseignant' : 'Saisie administration';
    }

    public function getMotifCategorieLabelAttribute(): string
    {
        return match ($this->motif_categorie) {
            'maladie' => 'Maladie',
            'personnel' => 'Personnel',
            'mission' => 'Mission',
            'autre' => 'Autre',
            default => '—',
        };
    }

    public function getPeriodeLabelAttribute(): string
    {
        $debut = $this->date_debut?->format('d/m/Y');
        $fin = $this->date_fin?->format('d/m/Y');

        if (!$debut) {
            return '—';
        }

        if ($debut === $fin) {
            return $debut;
        }

        return $debut . ' → ' . $fin;
    }
}
