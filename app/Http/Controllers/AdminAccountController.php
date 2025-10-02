<?php

namespace App\Http\Controllers;

use App\Models\PersonnelAdministration;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminAccountController extends Controller
{
    /**
     * Display a listing of admin accounts.
     */
    public function index()
    {
        $adminAccounts = PersonnelAdministration::with('utilisateur')
            ->whereHas('utilisateur', function($query) {
                $query->where('role', 'personnel_admin');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.accounts.index', compact('adminAccounts'));
    }

    /**
     * Show the form for creating a new admin account.
     */
    public function create()
    {
        $permissions = $this->getAvailablePermissions();
        return view('admin.accounts.create', compact('permissions'));
    }

    /**
     * Store a newly created admin account.
     */
    public function store(Request $request)
    {
        // Debug: Log de la requête
        \Log::info('Store method called', [
            'method' => $request->method(),
            'data' => $request->all(),
            'has_file' => $request->hasFile('photo_profil')
        ]);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'nullable|string|max:20',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date',
            'adresse' => 'nullable|string|max:500',
            'poste' => 'required|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'required|date',
            'salaire' => 'nullable|numeric|min:0',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', $this->getAllPermissionKeys()),
            'observations' => 'nullable|string|max:1000',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'required|string|min:8|confirmed'
        ]);

        try {
            // Créer l'utilisateur avec le rôle personnel_admin
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'sexe' => $request->sexe,
                'date_naissance' => $request->date_naissance,
                'adresse' => $request->adresse,
                'role' => 'personnel_admin',
                'actif' => true
            ]);

            // Gérer la photo de profil
            $photoPath = null;
            if ($request->hasFile('photo_profil')) {
                $photoPath = $request->file('photo_profil')->store('photos/admin', 'public');
            }

            // Créer le profil personnel d'administration avec permissions
            $permissions = $request->permissions ?? [];
            PersonnelAdministration::create([
                'utilisateur_id' => $utilisateur->id,
                'poste' => $request->poste,
                'departement' => $request->departement,
                'date_embauche' => $request->date_embauche,
                'salaire' => $request->salaire,
                'permissions' => $permissions,
                'observations' => $request->observations
            ]);

            // Mettre à jour la photo de profil si elle existe
            if ($photoPath) {
                $utilisateur->update(['photo_profil' => $photoPath]);
            }

            return redirect()->route('admin.accounts.index')
                ->with('success', 'Compte administrateur créé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified admin account.
     */
    public function show(PersonnelAdministration $adminAccount)
    {
        $adminAccount->load('utilisateur');
        $permissions = $this->getAvailablePermissions();
        return view('admin.accounts.show', compact('adminAccount', 'permissions'));
    }

    /**
     * Show the form for editing the specified admin account.
     */
    public function edit(PersonnelAdministration $adminAccount)
    {
        $adminAccount->load('utilisateur');
        $permissions = $this->getAvailablePermissions();
        
        // Décoder les permissions existantes
        $existingPermissions = $adminAccount->permissions;
        if (is_string($existingPermissions)) {
            $existingPermissions = json_decode($existingPermissions, true);
        }
        if (!is_array($existingPermissions)) {
            $existingPermissions = [];
        }
        
        
        return view('admin.accounts.edit', compact('adminAccount', 'permissions', 'existingPermissions'));
    }

    /**
     * Update the specified admin account.
     */
    public function update(Request $request, PersonnelAdministration $adminAccount)
    {
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $adminAccount->utilisateur_id,
            'telephone' => 'nullable|string|max:20',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date',
            'adresse' => 'nullable|string|max:500',
            'poste' => 'required|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'required|date',
            'salaire' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,inactif,suspendu',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', $this->getAllPermissionKeys()),
            'observations' => 'nullable|string|max:1000',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Mettre à jour l'utilisateur
            $adminAccount->utilisateur->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'sexe' => $request->sexe,
                'date_naissance' => $request->date_naissance,
                'adresse' => $request->adresse,
                'actif' => $request->statut === 'actif'
            ]);

            // Gérer la photo de profil
            if ($request->hasFile('photo_profil')) {
                // Supprimer l'ancienne photo
                if ($adminAccount->utilisateur->photo_profil) {
                    Storage::disk('public')->delete($adminAccount->utilisateur->photo_profil);
                }
                
                $photoPath = $request->file('photo_profil')->store('photos/admin', 'public');
                $adminAccount->utilisateur->update(['photo_profil' => $photoPath]);
                
                // Synchroniser l'image pour XAMPP
                \App\Helpers\ImageSyncHelper::syncImage($photoPath);
            }

            // Mettre à jour le profil personnel d'administration
            $adminAccount->update([
                'poste' => $request->poste,
                'departement' => $request->departement,
                'date_embauche' => $request->date_embauche,
                'salaire' => $request->salaire,
                'statut' => $request->statut,
                'permissions' => $request->permissions,
                'observations' => $request->observations
            ]);

            return redirect()->route('admin.accounts.index')
                ->with('success', 'Compte administrateur mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified admin account.
     */
    public function destroy(PersonnelAdministration $adminAccount)
    {
        try {
            // Empêcher la suppression de l'administrateur principal
            if ($adminAccount->utilisateur->email === 'admin@gmail.com') {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer l\'administrateur principal.');
            }

            // Supprimer la photo de profil
            if ($adminAccount->utilisateur->photo_profil) {
                Storage::disk('public')->delete($adminAccount->utilisateur->photo_profil);
            }

            // Supprimer l'utilisateur (cascade supprimera le personnel d'administration)
            $adminAccount->utilisateur->delete();

            return redirect()->route('admin.accounts.index')
                ->with('success', 'Compte administrateur supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Gérer les permissions d'un compte administrateur
     */
    public function managePermissions(PersonnelAdministration $adminAccount)
    {
        $adminAccount->load('utilisateur');
        $permissions = $this->getAvailablePermissions();
        return view('admin.accounts.permissions', compact('adminAccount', 'permissions'));
    }

    /**
     * Mettre à jour les permissions
     */
    public function updatePermissions(Request $request, PersonnelAdministration $adminAccount)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', $this->getAllPermissionKeys())
        ]);

        $adminAccount->update([
            'permissions' => $request->permissions
        ]);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Permissions mises à jour avec succès');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(PersonnelAdministration $adminAccount)
    {
        $newPassword = 'admin123';
        $adminAccount->utilisateur->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé. Nouveau mot de passe: ' . $newPassword);
    }

    /**
     * Activer/Désactiver un compte
     */
    public function toggleStatus(PersonnelAdministration $adminAccount)
    {
        $newStatus = $adminAccount->statut === 'actif' ? 'inactif' : 'actif';
        $adminAccount->update(['statut' => $newStatus]);
        $adminAccount->utilisateur->update(['actif' => $newStatus === 'actif']);

        $message = $newStatus === 'actif' ? 'Compte activé' : 'Compte désactivé';
        return redirect()->back()->with('success', $message . ' avec succès');
    }

    /**
     * Obtenir toutes les permissions disponibles
     */
    private function getAvailablePermissions()
    {
        return [
            'Gestion des utilisateurs' => [
                'utilisateurs.view' => 'Voir les utilisateurs',
                'utilisateurs.create' => 'Créer des utilisateurs',
                'utilisateurs.edit' => 'Modifier les utilisateurs',
                'utilisateurs.delete' => 'Supprimer les utilisateurs',
            ],
            'Gestion des élèves' => [
                'eleves.view' => 'Voir les élèves',
                'eleves.create' => 'Créer des élèves',
                'eleves.edit' => 'Modifier les élèves',
                'eleves.delete' => 'Supprimer les élèves',
            ],
            'Gestion des enseignants' => [
                'enseignants.view' => 'Voir les enseignants',
                'enseignants.create' => 'Créer des enseignants',
                'enseignants.edit' => 'Modifier les enseignants',
                'enseignants.delete' => 'Supprimer les enseignants',
                'enseignants.salaires' => 'Gérer les salaires des enseignants',
            ],
            'Gestion des salaires' => [
                'salaires.view' => 'Voir les salaires',
                'salaires.create' => 'Créer des salaires',
                'salaires.edit' => 'Modifier les salaires',
                'salaires.delete' => 'Supprimer les salaires',
                'salaires.valider' => 'Valider les salaires',
                'salaires.payer' => 'Payer les salaires',
                'salaires.rapports' => 'Voir les rapports de salaires',
            ],
            'Gestion des classes' => [
                'classes.view' => 'Voir les classes',
                'classes.create' => 'Créer des classes',
                'classes.edit' => 'Modifier les classes',
                'classes.delete' => 'Supprimer les classes',
            ],
            'Gestion des matières' => [
                'matieres.view' => 'Voir les matières',
                'matieres.create' => 'Créer des matières',
                'matieres.edit' => 'Modifier les matières',
                'matieres.delete' => 'Supprimer les matières',
            ],
            'Emploi du temps' => [
                'emplois_temps.view' => 'Voir les emplois du temps',
                'emplois_temps.create' => 'Créer les emplois du temps',
                'emplois_temps.edit' => 'Modifier les emplois du temps',
                'emplois_temps.delete' => 'Supprimer les emplois du temps',
            ],
            'Gestion des absences' => [
                'absences.view' => 'Voir les absences',
                'absences.create' => 'Saisir des absences',
                'absences.edit' => 'Modifier les absences',
                'absences.delete' => 'Supprimer les absences',
            ],
            'Gestion des notes' => [
                'notes.view' => 'Voir les notes',
                'notes.create' => 'Saisir des notes',
                'notes.edit' => 'Modifier les notes',
                'notes.delete' => 'Supprimer les notes',
                'notes.bulletins' => 'Générer les bulletins',
            ],
            'Gestion des paiements' => [
                'paiements.view' => 'Voir les paiements',
                'paiements.create' => 'Enregistrer des paiements',
                'paiements.edit' => 'Modifier les paiements',
                'paiements.delete' => 'Supprimer les paiements',
            ],
            'Comptabilité' => [
                'comptabilite.view' => 'Voir la comptabilité',
                'comptabilite.rapports' => 'Voir les rapports comptables',
                'comptabilite.entrees' => 'Voir les entrées',
                'comptabilite.sorties' => 'Voir les sorties',
                'entrees.view' => 'Voir les entrées',
                'entrees.create' => 'Créer des entrées',
                'entrees.edit' => 'Modifier les entrées',
                'entrees.delete' => 'Supprimer les entrées',
                'depenses.view' => 'Voir les dépenses',
                'depenses.create' => 'Créer des dépenses',
                'depenses.edit' => 'Modifier les dépenses',
                'depenses.delete' => 'Supprimer les dépenses',
            ],
            'Rapports' => [
                'rapports.view' => 'Voir les rapports',
                'rapports.financiers' => 'Rapports financiers',
                'rapports.eleves' => 'Rapports élèves',
                'rapports.enseignants' => 'Rapports enseignants',
            ],
            'Statistiques' => [
                'statistiques.view' => 'Voir les statistiques',
                'statistiques.financieres' => 'Statistiques financières',
                'statistiques.absences' => 'Statistiques des absences',
            ],
            'Cartes scolaires' => [
                'cartes-scolaires.view' => 'Voir les cartes scolaires',
                'cartes-scolaires.create' => 'Créer des cartes scolaires',
                'cartes-scolaires.edit' => 'Modifier les cartes scolaires',
                'cartes-scolaires.delete' => 'Supprimer les cartes scolaires',
            ],
            'Cartes enseignants' => [
                'cartes-enseignants.view' => 'Voir les cartes enseignants',
                'cartes-enseignants.create' => 'Créer des cartes enseignants',
                'cartes-enseignants.edit' => 'Modifier les cartes enseignants',
                'cartes-enseignants.delete' => 'Supprimer les cartes enseignants',
            ],
            'Notifications' => [
                'notifications.view' => 'Voir les notifications',
                'notifications.create' => 'Créer des notifications',
                'notifications.edit' => 'Modifier les notifications',
                'notifications.delete' => 'Supprimer les notifications',
            ],
            'Tarifs' => [
                'tarifs.view' => 'Voir les tarifs',
                'tarifs.create' => 'Créer des tarifs',
                'tarifs.edit' => 'Modifier les tarifs',
                'tarifs.delete' => 'Supprimer les tarifs',
            ],
            'Administration' => [
                'admin.accounts.view' => 'Voir les comptes administrateurs',
                'admin.accounts.create' => 'Créer des comptes administrateurs',
                'admin.accounts.edit' => 'Modifier les comptes administrateurs',
                'admin.accounts.delete' => 'Supprimer les comptes administrateurs',
                'admin.accounts.permissions' => 'Gérer les permissions des comptes administrateurs',
            ],
            'Établissement' => [
                'etablissement.view' => 'Voir les informations de l\'établissement',
                'etablissement.edit' => 'Modifier les informations de l\'établissement',
            ],
            'Années scolaires' => [
                'annees_scolaires.view' => 'Voir les années scolaires',
                'annees_scolaires.create' => 'Créer des années scolaires',
                'annees_scolaires.edit' => 'Modifier les années scolaires',
                'annees_scolaires.delete' => 'Supprimer les années scolaires',
            ]
        ];
    }

    /**
     * Obtenir toutes les clés de permissions pour la validation
     */
    private function getAllPermissionKeys()
    {
        $permissions = $this->getAvailablePermissions();
        $keys = [];
        
        foreach ($permissions as $category => $perms) {
            foreach ($perms as $key => $label) {
                $keys[] = $key;
            }
        }
        
        return $keys;
    }
}
