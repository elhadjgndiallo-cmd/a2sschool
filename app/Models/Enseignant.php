<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'annee_scolaire_id',
        'numero_employe',
        'date_embauche',
        'specialite',
        'statut',
        'salaire',
        'qualifications',
        'actif'
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire' => 'decimal:2',
        'actif' => 'boolean'
    ];

    /**
     * Relations
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'annee_scolaire_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function emploisTemps()
    {
        return $this->hasMany(EmploiTemps::class);
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere');
    }

    public function cartesEnseignants()
    {
        return $this->hasMany(CarteEnseignant::class);
    }

    public function salairesEnseignants()
    {
        return $this->hasMany(SalaireEnseignant::class);
    }

    public function bonsSalaire()
    {
        return $this->hasMany(BonSalaireEnseignant::class);
    }

    public function totalAvancesActives(): float
    {
        return (float) $this->bonsSalaire()->actifs()->sum('montant');
    }

    /**
     * Scope pour les enseignants actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Enseignants rattachés à une année scolaire donnée.
     */
    public function scopePourAnneeScolaire($query, ?int $anneeScolaireId)
    {
        if (!$anneeScolaireId) {
            return $query;
        }

        return $query->where('annee_scolaire_id', $anneeScolaireId);
    }

    /**
     * Enseignants de l'année scolaire active.
     */
    public function scopePourAnneeActive($query)
    {
        $annee = AnneeScolaire::anneeActive();

        if (!$annee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('annee_scolaire_id', $annee->id);
    }

    /**
     * Liste triée pour les listes déroulantes (actifs, année scolaire active).
     */
    public static function listeDeroulante(?int $anneeScolaireId = null): \Illuminate\Support\Collection
    {
        $query = static::query()
            ->with('utilisateur')
            ->actif();

        if ($anneeScolaireId) {
            $query->pourAnneeScolaire($anneeScolaireId);
        } else {
            $query->pourAnneeActive();
        }

        return $query->get()->sortBy(function (self $enseignant) {
            $user = $enseignant->utilisateur;
            if (!$user) {
                return '';
            }

            return strtolower(trim(($user->nom ?? '') . ' ' . ($user->prenom ?? $user->name ?? '')));
        })->values();
    }

    /**
     * Générer le prochain numéro d'employé (ex. ENS00001, ENS00002…)
     */
    public static function generateNextNumeroEmploye(): string
    {
        $prefix = 'ENS';
        $padLength = 5;

        $maxNum = static::query()
            ->where('numero_employe', 'like', $prefix . '%')
            ->pluck('numero_employe')
            ->map(function (string $numero) use ($prefix) {
                if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/i', $numero, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max() ?? 0;

        $next = $maxNum + 1;

        do {
            $numero = $prefix . str_pad((string) $next, $padLength, '0', STR_PAD_LEFT);
            $next++;
        } while (static::where('numero_employe', $numero)->exists());

        return $numero;
    }

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return $this->utilisateur->name;
    }
}
