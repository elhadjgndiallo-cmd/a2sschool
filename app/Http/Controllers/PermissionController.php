<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\PersonnelAdministration;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Afficher la liste des utilisateurs avec leurs permissions
     */
    public function index()
    {
        $utilisateurs = Utilisateur::with('personnelAdministration')
            ->whereIn('role', ['admin', 'personnel_admin'])
            ->get();

        return view('admin.permissions.index', compact('utilisateurs'));
    }

    /**
     * Afficher les permissions d'un utilisateur
     */
    public function show(Utilisateur $utilisateur)
    {
        if ($utilisateur->role !== 'personnel_admin') {
            return redirect()->back()->with('error', 'Cet utilisateur ne peut pas avoir de permissions personnalisées.');
        }

        $permissions = $this->getAllPermissions();
        $userPermissions = $utilisateur->personnelAdministration ? 
            $utilisateur->personnelAdministration->permissions : [];

        return view('admin.permissions.show', compact('utilisateur', 'permissions', 'userPermissions'));
    }

    /**
     * Mettre à jour les permissions d'un utilisateur
     */
    public function update(Request $request, Utilisateur $utilisateur)
    {
        if ($utilisateur->role !== 'personnel_admin') {
            return redirect()->back()->with('error', 'Cet utilisateur ne peut pas avoir de permissions personnalisées.');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|in:' . implode(',', $this->getAllPermissionKeys())
        ]);

        $permissions = $request->permissions ?? [];

        // Créer ou mettre à jour le personnel d'administration
        $personnelAdmin = $utilisateur->personnelAdministration;
        if (!$personnelAdmin) {
            $personnelAdmin = PersonnelAdministration::create([
                'utilisateur_id' => $utilisateur->id,
                'poste' => 'Personnel Administration',
                'departement' => 'Administration',
                'date_embauche' => now(),
                'statut' => 'actif',
                'permissions' => $permissions
            ]);
        } else {
            $personnelAdmin->update(['permissions' => $permissions]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions mises à jour avec succès.');
    }

    /**
     * Obtenir toutes les permissions disponibles
     */
    private function getAllPermissions()
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
                'eleves.notes' => 'Gérer les notes des élèves',
            ],
            'Gestion des enseignants' => [
                'enseignants.view' => 'Voir les enseignants',
                'enseignants.create' => 'Créer des enseignants',
                'enseignants.edit' => 'Modifier les enseignants',
                'enseignants.delete' => 'Supprimer les enseignants',
                'enseignants.salaires' => 'Gérer les salaires des enseignants',
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
                'emploi_temps.view' => 'Voir l\'emploi du temps',
                'emploi_temps.create' => 'Créer l\'emploi du temps',
                'emploi_temps.edit' => 'Modifier l\'emploi du temps',
                'emploi_temps.delete' => 'Supprimer l\'emploi du temps',
            ],
            'Comptabilité' => [
                'comptabilite.view' => 'Voir la comptabilité',
                'comptabilite.entrees' => 'Gérer les entrées',
                'comptabilite.sorties' => 'Gérer les sorties',
                'comptabilite.rapports' => 'Voir les rapports',
            ],
            'Rapports' => [
                'rapports.view' => 'Voir les rapports',
                'rapports.export' => 'Exporter les rapports',
            ],
            'Notifications' => [
                'notifications.view' => 'Voir les notifications',
                'notifications.create' => 'Créer des notifications',
                'notifications.send' => 'Envoyer des notifications',
            ],
            'Système' => [
                'system.settings' => 'Paramètres du système',
                'system.backup' => 'Sauvegarde du système',
                'system.logs' => 'Voir les logs',
            ]
        ];
    }

    /**
     * Obtenir toutes les clés de permissions
     */
    private function getAllPermissionKeys()
    {
        $permissions = $this->getAllPermissions();
        $keys = [];
        foreach ($permissions as $category => $perms) {
            $keys = array_merge($keys, array_keys($perms));
        }
        return $keys;
    }
}
