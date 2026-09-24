<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * Relation avec le profil élève (inscription de l'année scolaire active).
     */
    public function eleve()
    {
        $anneeId = \App\Models\AnneeScolaire::anneeActive()?->id;

        $relation = $this->hasOne(Eleve::class);

        if ($anneeId) {
            return $relation->where('annee_scolaire_id', $anneeId);
        }

        return $relation->latestOfMany();
    }

    /**
     * Toutes les inscriptions élève (toutes années).
     */
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    /**
     * Relation avec le profil enseignant (année scolaire active).
     */
    public function enseignant()
    {
        $anneeId = \App\Models\AnneeScolaire::anneeActive()?->id;

        $relation = $this->hasOne(Enseignant::class);

        if ($anneeId) {
            return $relation->where('annee_scolaire_id', $anneeId);
        }

        return $relation->latestOfMany();
    }

    /**
     * Tous les profils enseignant (toutes années).
     */
    public function enseignants()
    {
        return $this->hasMany(Enseignant::class);
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
     * Exclure le super-admin (compte système caché) des listes.
     */
    public function scopeMasquerSysteme($query)
    {
        return $query->where('role', '!=', 'super_admin');
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role)
    {
        if ($role === 'admin' && $this->isSuperAdmin()) {
            return true;
        }

        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur est un admin (visible)
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Super-admin caché : invisible dans les listes, peut gérer l'admin visible.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Administrateur principal (visible) : tous les droits sauf le compte système.
     */
    public function isPrincipalAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Seul l'administrateur système peut créer un administrateur principal.
     */
    public function canCreatePrincipalAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Interdire la création d'un administrateur principal hors compte système.
     */
    public static function abortUnlessCanCreatePrincipalAdmin(): void
    {
        $acteur = auth()->user();
        if (!$acteur || !$acteur->canCreatePrincipalAdmin()) {
            abort(403, 'Seul l\'administrateur système peut créer un compte administrateur principal.');
        }
    }

    /**
     * Ancien nom : le compte caché est désormais super_admin.
     */
    public function isSystemAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Interdire l'accès au super-admin, et à l'admin visible sauf pour le super-admin.
     */
    public static function abortIfSystemAdmin(?self $utilisateur): void
    {
        if (!$utilisateur) {
            return;
        }

        if ($utilisateur->isSuperAdmin()) {
            abort(403, 'Ce compte administrateur système n\'est pas accessible.');
        }

        $acteur = auth()->user();
        if ($utilisateur->isAdmin() && (!$acteur || !$acteur->isSuperAdmin())) {
            abort(403, 'Seul le compte administrateur système peut gérer cet administrateur.');
        }
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
        if ($this->role === 'admin' || $this->role === 'super_admin') {
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
            case 'super_admin':
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
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isPersonnelAdmin();
    }

    /**
     * Créer ou réparer le super-admin caché avec les identifiants canoniques.
     */
    public static function ensureHiddenSuperAdmin(
        string $email = 'systeme@a2schoolgn.com',
        string $password = 'Diallo224'
    ): self {
        $parRole = static::where('role', 'super_admin')->first();
        $parEmail = static::where('email', $email)->first();

        if ($parRole && $parEmail && (int) $parRole->id !== (int) $parEmail->id) {
            $parEmail->email = 'ancien-' . $parEmail->id . '-' . $email;
            $parEmail->save();
            $parEmail = null;
        }

        $user = $parRole ?? $parEmail;

        if ($user) {
            $user->fill([
                'email' => $email,
                'password' => $password,
                'role' => 'super_admin',
                'actif' => true,
                'nom' => $user->nom ?: 'Système',
                'prenom' => $user->prenom ?: 'Administrateur',
                'name' => $user->name ?: 'Administrateur Système',
            ]);
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();

            return $user;
        }

        return static::create([
            'nom' => 'Système',
            'prenom' => 'Administrateur',
            'name' => 'Administrateur Système',
            'email' => $email,
            'password' => $password,
            'role' => 'super_admin',
            'email_verified_at' => now(),
            'actif' => true,
        ]);
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    /**
     * Libère les clés étrangères pointant vers cet utilisateur (dépenses, factures, etc.)
     * afin de permettre une suppression définitive sans contrainte 1451.
     */
    public function detacherReferencesAvantSuppression(?int $remplacantId = null): void
    {
        $utilisateurId = (int) $this->id;
        $remplacant = $remplacantId && $remplacantId !== $utilisateurId
            ? $remplacantId
            : (auth()->id() && auth()->id() !== $utilisateurId ? (int) auth()->id() : null);

        $colonnes = [
            ['depenses', 'approuve_par'],
            ['depenses', 'paye_par'],
            ['salaires_enseignants', 'calcule_par'],
            ['salaires_enseignants', 'valide_par'],
            ['salaires_enseignants', 'paye_par'],
            ['cartes_scolaires', 'emise_par'],
            ['cartes_scolaires', 'validee_par'],
            ['cartes_enseignants', 'emise_par'],
            ['cartes_enseignants', 'validee_par'],
            ['cartes_personnel_administration', 'emise_par'],
            ['cartes_personnel_administration', 'validee_par'],
            ['absences_enseignants', 'traite_par'],
            ['bons_salaire_enseignants', 'cree_par'],
            ['entrees', 'enregistre_par'],
            ['paiements', 'encaisse_par'],
            ['absences', 'saisi_par'],
            ['recus_rappel', 'genere_par'],
            ['absences_enseignants', 'saisi_par'],
            ['factures', 'genere_par'],
            ['test_mensuels', 'created_by'],
            ['documents', 'createur_id'],
            ['evenements', 'createur_id'],
        ];

        foreach ($colonnes as [$table, $column]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            try {
                DB::table($table)->where($column, $utilisateurId)->update([$column => null]);
            } catch (\Throwable $e) {
                if ($remplacant) {
                    DB::table($table)->where($column, $utilisateurId)->update([$column => $remplacant]);
                }
            }
        }

        if (Schema::hasTable('messages')) {
            DB::table('messages')->where(function ($q) use ($utilisateurId) {
                $q->where('expediteur_id', $utilisateurId)
                  ->orWhere('destinataire_id', $utilisateurId);
            })->delete();
        }

        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'utilisateur_id')) {
            DB::table('notifications')->where('utilisateur_id', $utilisateurId)->delete();
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $utilisateurId)->delete();
        }
    }

}