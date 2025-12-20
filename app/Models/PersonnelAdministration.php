<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelAdministration extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'personnel_administration';

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

    /**
     * Obtenir la clé de route pour le modèle
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Relation avec l'utilisateur
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class);
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
}
