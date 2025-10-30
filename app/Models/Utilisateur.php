<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'utilisateurs'; // On s'assure que le nom de la table est correct

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nom',
        'prenom', 
        'email',
        'password',
        'telephone',
        'adresse',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'photo_profil',
        'role', // admin, teacher, student, parent
        'actif',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_naissance' => 'date',
        'actif' => 'boolean'
    ];

    /**
     * Accesseur pour récupérer le nom à partir du champ name ou nom
     */
    public function getNomAttribute($value)
    {
        // Si le champ nom est vide mais name existe, extraire le nom du champ name
        if (empty($value) && !empty($this->attributes['name'])) {
            $nameParts = explode(' ', $this->attributes['name']);
            // Le nom est généralement le dernier élément
            return end($nameParts);
        }
        return $value;
    }

    /**
     * Accesseur pour récupérer le prénom à partir du champ name ou prenom
     */
    public function getPrenomAttribute($value)
    {
        // Si le champ prenom est vide mais name existe, extraire le prénom du champ name
        if (empty($value) && !empty($this->attributes['name'])) {
            $nameParts = explode(' ', $this->attributes['name']);
            // Le prénom est généralement le premier élément (ou tous sauf le dernier)
            array_pop($nameParts); // Enlever le dernier élément (nom)
            return implode(' ', $nameParts);
        }
        return $value;
    }

    /**
     * Mutateur pour mettre à jour le champ name quand nom ou prenom change
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = $value;
        $this->updateNameField();
    }

    /**
     * Mutateur pour mettre à jour le champ name quand nom ou prenom change
     */
    public function setPrenomAttribute($value)
    {
        $this->attributes['prenom'] = $value;
        $this->updateNameField();
    }

    /**
     * Mettre à jour le champ name basé sur nom et prenom
     */
    protected function updateNameField()
    {
        $prenom = $this->attributes['prenom'] ?? '';
        $nom = $this->attributes['nom'] ?? '';
        
        if (!empty($prenom) || !empty($nom)) {
            $this->attributes['name'] = trim($prenom . ' ' . $nom);
        }
    }

    /**
     * Relation avec le profil élève
     */
    public function eleve()
    {
        return $this->hasOne(Eleve::class);
    }

    /**
     * Relation avec le profil enseignant
     */
    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }

    /**
     * Relation avec le profil parent
     */
    public function parent()
    {
        return $this->hasOne(ParentModel::class);
    }

    /**
     * Relation avec le profil personnel d'administration
     */
    public function personnelAdministration()
    {
        return $this->hasOne(PersonnelAdministration::class);
    }

    /**
     * Relation avec les absences saisies
     */
    public function absencesSaisies()
    {
        return $this->hasMany(Absence::class, 'saisi_par');
    }

    /**
     * Relation avec les paiements encaissés
     */
    public function paiementsEncaisses()
    {
        return $this->hasMany(Paiement::class, 'encaisse_par');
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Vérifier si l'utilisateur est un enseignant
     */
    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    /**
     * Vérifier si l'utilisateur est un élève
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Vérifier si l'utilisateur est un parent
     */
    public function isParent()
    {
        return $this->hasRole('parent');
    }

    /**
     * Vérifier si l'utilisateur est un personnel d'administration
     */
    public function isPersonnelAdmin()
    {
        return $this->hasRole('personnel_admin');
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permission)
    {
        // Les administrateurs ont toutes les permissions
        if ($this->role === 'admin') {
            return true;
        }
        
        // Vérifier les permissions pour le personnel d'administration
        if ($this->role === 'personnel_admin' && $this->personnelAdministration) {
            return $this->personnelAdministration->hasPermission($permission);
        }
        
        // Permissions pour les enseignants
        if ($this->role === 'teacher') {
            return $this->hasTeacherPermission($permission);
        }
        
        // Permissions basées sur les rôles pour les événements
        if (str_starts_with($permission, 'evenements.')) {
            return $this->hasEvenementPermission($permission);
        }
        
        return false;
    }

    /**
     * Vérifier les permissions spécifiques aux enseignants
     */
    private function hasTeacherPermission($permission)
    {
        // Permissions de base pour les enseignants
        $teacherPermissions = [
            'notes.view',
            'notes.create',
            'notes.edit',
            'eleves.view',
            'absences.view',
            'absences.create',
            'absences.edit',
            'emplois-temps.view',
            'emplois-temps.create',
            'emplois-temps.edit'
        ];
        
        return in_array($permission, $teacherPermissions);
    }

    /**
     * Vérifier les permissions spécifiques aux événements
     */
    private function hasEvenementPermission($permission)
    {
        switch ($this->role) {
            case 'admin':
            case 'personnel_admin':
                // Toutes les permissions pour les admins et personnel admin
                return true;
                
            case 'teacher':
                // Les enseignants peuvent voir et créer des événements
                return in_array($permission, ['evenements.view', 'evenements.create']);
                
            case 'parent':
            case 'student':
                // Les parents et élèves peuvent seulement voir les événements
                return $permission === 'evenements.view';
                
            default:
                return false;
        }
    }

    /**
     * Vérifier si l'utilisateur peut accéder aux fonctionnalités d'administration
     */
    public function canAccessAdmin()
    {
        return $this->isAdmin() || $this->isPersonnelAdmin();
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

}