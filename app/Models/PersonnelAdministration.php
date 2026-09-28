<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PersonnelAdministration extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'personnel_administration';

    public const CYCLES = ['primaire', 'college', 'lycee'];

    protected static ?bool $cycleColumnExists = null;

    protected $fillable = [
        'utilisateur_id',
        'poste',
        'departement',
        'date_embauche',
        'salaire',
        'statut',
        'permissions',
        'observations'
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire' => 'decimal:2',
        'permissions' => 'array'
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            unset($model->attributes['cycle']);
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        $found = $this->where($field, $value)->first();
        if ($found) {
            return $found;
        }

        return static::where('utilisateur_id', $value)->first();
    }

    /**
     * Relation avec l'utilisateur
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
    }

    /**
     * Exclure l'administrateur système des listes visibles.
     */
    public function scopeVisibleAuxAdmins($query)
    {
        return $query->whereHas('utilisateur', function ($q) {
            $q->where('role', '!=', 'super_admin');
        });
    }

    /**
     * Interdire l'accès si ce profil est lié à l'administrateur système.
     */
    public function abortIfSystemAdmin(): void
    {
        Utilisateur::abortIfSystemAdmin($this->utilisateur);
    }

    /**
     * Vérifier si le personnel a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }
        
        $permissions = is_string($this->permissions) ? json_decode($this->permissions, true) : $this->permissions;
        
        if (!is_array($permissions)) {
            return false;
        }

        // Support rétrocompatibilité: normaliser les anciennes clés vers les nouvelles
        $normalizedPermissions = array_map(function (string $key): string {
            // Anciennes clés admin_accounts.* -> admin.accounts.*
            if (str_starts_with($key, 'admin_accounts.')) {
                $key = str_replace('admin_accounts.', 'admin.accounts.', $key);
            }
            // Anciennes clés cartes_enseignants.* -> cartes-enseignants.*
            if (str_starts_with($key, 'cartes_enseignants.')) {
                $key = str_replace('cartes_enseignants.', 'cartes-enseignants.', $key);
            }
            // Anciennes clés emploi_temps.* -> emplois-temps.*
            if (str_starts_with($key, 'emploi_temps.')) {
                $key = str_replace('emploi_temps.', 'emplois-temps.', $key);
            }
            // Anciennes clés emplois_temps.* -> emplois-temps.*
            if (str_starts_with($key, 'emplois_temps.')) {
                $key = str_replace('emplois_temps.', 'emplois-temps.', $key);
            }
            return $key;
        }, $permissions);

        return in_array($permission, $normalizedPermissions, true);
    }

    /**
     * Ajouter une permission
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Retirer une permission
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Relation avec les cartes personnel d'administration
     */
    public function cartesPersonnelAdministration()
    {
        return $this->hasMany(CartePersonnelAdministration::class);
    }

    /**
     * Obtenir le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return $this->utilisateur->nom . ' ' . $this->utilisateur->prenom;
    }

    public static function profils(): array
    {
        return [
            'censeur' => [
                'poste' => 'Censeur',
                'cycle' => 'lycee',
                'label' => 'Censeur (Lycée)',
            ],
            'directeur_etudes' => [
                'poste' => 'Directeur d\'études',
                'cycle' => 'college',
                'label' => 'Directeur d\'études (Collège)',
            ],
            'directeur_primaire' => [
                'poste' => 'Directeur du primaire',
                'cycle' => 'primaire',
                'label' => 'Directeur du primaire',
            ],
        ];
    }

    public static function resoudreProfil(?string $profil, ?string $posteLibre = null): array
    {
        $profils = self::profils();
        if ($profil && isset($profils[$profil])) {
            return $profils[$profil];
        }

        return [
            'poste' => $posteLibre ?: '',
            'cycle' => null,
            'label' => 'Autre',
        ];
    }

    public static function cleProfilDepuisPoste(?string $poste): string
    {
        foreach (self::profils() as $cle => $profil) {
            if (mb_strtolower(trim((string) $poste)) === mb_strtolower($profil['poste'])) {
                return $cle;
            }
        }

        return 'autre';
    }

    public static function permissionsParDefautCycle(): array
    {
        return [
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            'notes.view',
            'notes.create',
            'notes.edit',
            'notes.delete',
            'notes.bulletins',
            'emplois_temps.view',
            'emplois_temps.create',
            'emplois_temps.edit',
            'emplois_temps.delete',
            'emplois-temps.view',
            'emplois-temps.create',
            'emplois-temps.edit',
            'emplois-temps.delete',
        ];
    }

    /**
     * Accessor : $personnel->cycle ne doit jamais être pris pour une relation Eloquent.
     */
    public function getCycleAttribute($value): ?string
    {
        return in_array($value, self::CYCLES, true) ? $value : null;
    }

    public function estLimiteParCycle(): bool
    {
        return $this->cycleCode() !== null;
    }

    public function cycleLibelle(): string
    {
        return match ($this->cycleCode()) {
            'primaire' => 'Primaire',
            'college' => 'Collège',
            'lycee' => 'Lycée',
            default => '—',
        };
    }

    /**
     * Cycle pédagogique. Ne pas nommer cette méthode cycle() :
     * sans colonne en base, Eloquent la prendrait pour une relation et ferait une erreur 500.
     */
    public function cycleCode(): ?string
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists('cycle', $attributes)) {
            return null;
        }

        $cycle = $attributes['cycle'];

        return in_array($cycle, self::CYCLES, true) ? $cycle : null;
    }

    public static function hasCycleColumn(): bool
    {
        if (self::$cycleColumnExists !== null) {
            return self::$cycleColumnExists;
        }

        try {
            self::$cycleColumnExists = Schema::hasTable((new static)->getTable())
                && Schema::hasColumn((new static)->getTable(), 'cycle');
        } catch (\Throwable $e) {
            self::$cycleColumnExists = false;
        }

        return (bool) self::$cycleColumnExists;
    }

    public static function ensureCycleColumn(): void
    {
        try {
            if (!Schema::hasTable((new static)->getTable())) {
                return;
            }

            if (Schema::hasColumn((new static)->getTable(), 'cycle')) {
                self::$cycleColumnExists = true;

                return;
            }

            Schema::table((new static)->getTable(), function (Blueprint $table) {
                $table->string('cycle', 20)->nullable();
            });

            self::$cycleColumnExists = true;
        } catch (\Throwable $e) {
            self::$cycleColumnExists = false;
            \Log::warning('Impossible d\'ajouter la colonne cycle à personnel_administration', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function payloadAvecCycle(array $data, ?string $cycle): array
    {
        unset($data['cycle']);

        return $data;
    }

    /**
     * Enregistre le cycle hors insertion Eloquent (la colonne peut manquer en production).
     */
    public static function appliquerCycle(self $personnel, ?string $cycle): void
    {
        if ($cycle === null || $cycle === '') {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE personnel_administration ADD COLUMN cycle VARCHAR(20) NULL'
            );
        } catch (\Throwable $e) {
            // Colonne déjà présente, ou droits insuffisants.
        }

        try {
            \Illuminate\Support\Facades\DB::table('personnel_administration')
                ->where('id', $personnel->id)
                ->update(['cycle' => $cycle]);
        } catch (\Throwable $e) {
            \Log::warning('Cycle non enregistré', [
                'personnel_id' => $personnel->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
