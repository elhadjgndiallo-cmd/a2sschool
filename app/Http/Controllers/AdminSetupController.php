<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Etablissement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\PersonnelAdministration;

class AdminSetupController extends Controller
{
    /**
     * Afficher la page de configuration initiale
     */
    public function index()
    {
        // Vérifier si un admin existe déjà
        $adminExists = Utilisateur::where('role', 'admin')
            ->orWhere('role', 'personnel_admin')
            ->exists();

        if ($adminExists) {
            return redirect()->route('login')->with('info', 'L\'administrateur principal existe déjà.');
        }

        return view('admin.setup');
    }

    /**
     * Créer le compte administrateur principal
     */
    public function store(Request $request)
    {
        // Vérifier si un admin existe déjà
        $adminExists = Utilisateur::where('role', 'admin')
            ->orWhere('role', 'personnel_admin')
            ->exists();

        if ($adminExists) {
            return redirect()->route('login')->with('error', 'L\'administrateur principal existe déjà.');
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'nom_etablissement' => 'required|string|max:255',
            'adresse_etablissement' => 'required|string|max:500',
            'telephone_etablissement' => 'nullable|string|max:20',
            'email_etablissement' => 'nullable|email|max:255',
            'slogan_etablissement' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Créer l'établissement
            $etablissement = Etablissement::create([
                'nom' => $request->nom_etablissement,
                'adresse' => $request->adresse_etablissement,
                'telephone' => $request->telephone_etablissement,
                'email' => $request->email_etablissement,
                'slogan' => $request->slogan_etablissement,
                'statut_etablissement' => 'prive',
                'actif' => true,
            ]);

            // Créer l'utilisateur administrateur
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'actif' => true,
            ]);

            // Créer le profil personnel d'administration avec toutes les permissions
            $allPermissions = $this->getAllSystemPermissions();
            PersonnelAdministration::create([
                'utilisateur_id' => $utilisateur->id,
                'poste' => 'Administrateur Principal',
                'departement' => 'Direction',
                'date_embauche' => now(),
                'statut' => 'actif',
                'permissions' => $allPermissions,
                'observations' => 'Administrateur principal créé lors de la configuration initiale du système'
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Compte administrateur principal créé avec succès. Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création du compte administrateur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Obtenir toutes les permissions du système
     */
    private function getAllSystemPermissions()
    {
        return [
            // Permissions existantes (79 permissions)
            'evenements.view', 'evenements.create', 'evenements.edit', 'evenements.delete', 'evenements.manage_all',
            'notes.view', 'notes.create', 'notes.edit', 'notes.delete', 'notes.bulletins', 'notes.statistiques',
            'enseignants.view', 'enseignants.create', 'enseignants.edit', 'enseignants.delete', 'enseignants.salaires',
            'eleves.view', 'eleves.create', 'eleves.edit', 'eleves.delete', 'eleves.reinscription',
            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            'matieres.view', 'matieres.create', 'matieres.edit', 'matieres.delete',
            'absences.view', 'absences.create', 'absences.edit', 'absences.delete',
            'paiements.view', 'paiements.create', 'paiements.edit', 'paiements.delete',
            'rapports.view', 'rapports.financiers', 'rapports.eleves', 'rapports.enseignants',
            'cartes-scolaires.view', 'cartes-scolaires.create', 'cartes-scolaires.edit', 'cartes-scolaires.delete',
            'cartes-enseignants.view', 'cartes-enseignants.create', 'cartes-enseignants.edit', 'cartes-enseignants.delete',
            'entrees.view', 'entrees.create', 'entrees.edit', 'entrees.delete',
            'depenses.view', 'depenses.create', 'depenses.edit', 'depenses.delete',
            'admin.accounts.view', 'admin.accounts.create', 'admin.accounts.edit', 'admin.accounts.delete',
            'etablissement.view', 'etablissement.edit',
            'annees_scolaires.view', 'annees_scolaires.create', 'annees_scolaires.edit', 'annees_scolaires.delete',
            'tarifs.view', 'tarifs.create', 'tarifs.edit', 'tarifs.delete',
            'messages.view', 'messages.create', 'messages.edit', 'messages.delete',
            'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',
            
            // Nouvelles permissions (38 permissions)
            'utilisateurs.view', 'utilisateurs.create', 'utilisateurs.edit', 'utilisateurs.delete',
            'eleves.notes',
            'emploi_temps.view', 'emploi_temps.create', 'emploi_temps.edit', 'emploi_temps.delete',
            'comptabilite.view', 'comptabilite.entrees', 'comptabilite.sorties', 'comptabilite.rapports',
            'rapports.export',
            'notifications.send',
            'system.settings', 'system.backup', 'system.logs'
        ];
    }
}