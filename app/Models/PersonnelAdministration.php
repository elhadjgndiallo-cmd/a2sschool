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
        
        return in_array($permission, $permissions);
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
     * Obtenir le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return $this->utilisateur->nom . ' ' . $this->utilisateur->prenom;
    }
}
