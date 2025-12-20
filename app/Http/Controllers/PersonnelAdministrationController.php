<?php

namespace App\Http\Controllers;

use App\Models\PersonnelAdministration;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PersonnelAdministrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personnel = PersonnelAdministration::with('utilisateur')
            ->join('utilisateurs', 'personnel_administration.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.prenom', 'asc')
            ->orderBy('utilisateurs.nom', 'asc')
            ->select('personnel_administration.*')
            ->distinct()
            ->paginate(10);
            
        return view('personnel-administration.index', compact('personnel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->getAvailablePermissions();
        return view('personnel-administration.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys($this->getAvailablePermissions())),
            'observations' => 'nullable|string|max:1000',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Créer l'utilisateur
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Mot de passe par défaut
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
                $photoPath = $request->file('photo_profil')->store('photos/personnel', 'public');
            }

            // Créer le profil personnel d'administration
            PersonnelAdministration::create([
                'utilisateur_id' => $utilisateur->id,
                'poste' => $request->poste,
                'departement' => $request->departement,
                'date_embauche' => $request->date_embauche,
                'salaire' => $request->salaire,
                'permissions' => $request->permissions ?? [],
                'observations' => $request->observations
            ]);

            // Mettre à jour la photo de profil si elle existe
            if ($photoPath) {
                $utilisateur->update(['photo_profil' => $photoPath]);
            }

            return redirect()->route('personnel-administration.index')
                ->with('success', 'Personnel d\'administration créé avec succès. Mot de passe par défaut: password123');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PersonnelAdministration $personnelAdministration)
    {
        $personnelAdministration->load('utilisateur');
        return view('personnel-administration.show', compact('personnelAdministration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PersonnelAdministration $personnelAdministration)
    {
        $personnelAdministration->load('utilisateur');
        return view('personnel-administration.edit', compact('personnelAdministration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PersonnelAdministration $personnelAdministration)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $personnelAdministration->utilisateur_id,
            'telephone' => 'nullable|string|max:20',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date',
            'adresse' => 'nullable|string|max:500',
            'poste' => 'required|string|max:255',
            'departement' => 'nullable|string|max:255',
            'date_embauche' => 'required|date',
            'salaire' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,inactif,suspendu',
            'observations' => 'nullable|string|max:1000',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Mettre à jour l'utilisateur
            $personnelAdministration->utilisateur->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'sexe' => $request->sexe,
                'date_naissance' => $request->date_naissance,
                'adresse' => $request->adresse
            ]);

            // Gérer la photo de profil
            if ($request->hasFile('photo_profil')) {
                // Supprimer l'ancienne photo
                if ($personnelAdministration->utilisateur->photo_profil) {
                    Storage::disk('public')->delete($personnelAdministration->utilisateur->photo_profil);
                }
                
                $photoPath = $request->file('photo_profil')->store('photos/personnel', 'public');
                $personnelAdministration->utilisateur->update(['photo_profil' => $photoPath]);
            }

            // Mettre à jour le profil personnel d'administration
            $personnelAdministration->update([
                'poste' => $request->poste,
                'departement' => $request->departement,
                'date_embauche' => $request->date_embauche,
                'salaire' => $request->salaire,
                'statut' => $request->statut,
                'observations' => $request->observations
            ]);

            return redirect()->route('personnel-administration.index')
                ->with('success', 'Personnel d\'administration mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un personnel d'administration (désactivation)
     */
    public function destroy($id)
    {
        $personnelAdministration = PersonnelAdministration::findOrFail($id);
        
        DB::transaction(function() use ($personnelAdministration) {
            // Désactiver au lieu de supprimer
            $personnelAdministration->update(['statut' => 'inactif']);
            $personnelAdministration->utilisateur->update(['actif' => false]);
        });

        return redirect()->route('personnel-administration.index')
            ->with('success', 'Personnel d\'administration désactivé avec succès');
    }

    /**
     * Supprimer définitivement un personnel d'administration
     */
    public function deletePermanently($id)
    {
        $personnelAdministration = PersonnelAdministration::findOrFail($id);
        
        DB::transaction(function() use ($personnelAdministration) {
            // Supprimer les cartes associées
            $personnelAdministration->cartesPersonnelAdministration()->delete();
            
            // Supprimer la photo de profil si elle existe
            if ($personnelAdministration->utilisateur && $personnelAdministration->utilisateur->photo_profil) {
                if (Storage::disk('public')->exists($personnelAdministration->utilisateur->photo_profil)) {
                    Storage::disk('public')->delete($personnelAdministration->utilisateur->photo_profil);
                }
            }
            
            // Supprimer l'utilisateur associé
            if ($personnelAdministration->utilisateur) {
                $personnelAdministration->utilisateur->delete();
            }
            
            // Supprimer le personnel d'administration
            $personnelAdministration->delete();
        });

        return redirect()->route('personnel-administration.index')
            ->with('success', 'Personnel d\'administration supprimé définitivement avec succès');
    }

    /**
     * Gérer les permissions d'un personnel
     */
    public function managePermissions(PersonnelAdministration $personnelAdministration)
    {
        $personnelAdministration->load('utilisateur');
        $permissions = $this->getAvailablePermissions();
        
        // Debug: vérifier les permissions actuelles
        \Log::info('Permissions du personnel:', [
            'personnel_id' => $personnelAdministration->id,
            'permissions_raw' => $personnelAdministration->permissions,
            'permissions_type' => gettype($personnelAdministration->permissions),
            'permissions_count' => is_array($personnelAdministration->permissions) ? count($personnelAdministration->permissions) : 'N/A'
        ]);
        
        return view('personnel-administration.permissions', compact('personnelAdministration', 'permissions'));
    }

    /**
     * Mettre à jour les permissions
     */
    public function updatePermissions(Request $request, PersonnelAdministration $personnelAdministration)
    {
        // Debug simple
        \Log::info('=== UPDATE PERMISSIONS ===');
        \Log::info('Données reçues:', $request->all());
        
        // Récupérer les permissions
        $permissions = $request->input('permissions', []);
        
        // Debug: voir ce qui est reçu
        \Log::info('Permissions reçues dans updatePermissions:', [
            'raw_permissions' => $request->input('permissions'),
            'all_input' => $request->all(),
            'permissions_count' => count($permissions),
            'permissions_array' => $permissions
        ]);
        
        // Nettoyer les permissions (enlever les valeurs vides)
        $permissions = array_filter($permissions, function($value) {
            return !empty($value) && $value !== '';
        });
        
        \Log::info('Permissions après nettoyage dans updatePermissions:', [
            'permissions' => $permissions,
            'count' => count($permissions),
            'is_empty' => empty($permissions)
        ]);
        
        // Si aucune permission valide n'est trouvée, sauvegarder un tableau vide
        if (empty($permissions)) {
            $permissions = []; // Sauvegarder un tableau vide (aucune permission)
            \Log::info('Aucune permission valide dans updatePermissions, sauvegarde d\'un tableau vide');
        } else {
            \Log::info('Permissions valides trouvées dans updatePermissions, utilisation des permissions sélectionnées');
        }
        
        \Log::info('Permissions finales à sauvegarder dans updatePermissions:', $permissions);
        
        \Log::info('Permissions finales:', $permissions);
        
        // Mettre à jour les permissions
        try {
            $personnelAdministration->update([
                'permissions' => $permissions
            ]);
            
            \Log::info('Permissions sauvegardées avec succès:', [
                'personnel_id' => $personnelAdministration->id,
                'permissions' => $permissions,
                'permissions_count' => count($permissions)
            ]);
            
            return redirect()->route('personnel-administration.index')
                ->with('success', 'Permissions mises à jour avec succès (' . count($permissions) . ' permissions)');
                
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la sauvegarde:', ['error' => $e->getMessage()]);
            return back()->withErrors(['permissions' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()]);
        }
    }

    /**
     * Réactiver un personnel d'administration
     */
    public function reactivate($id)
    {
        $personnel = PersonnelAdministration::findOrFail($id);
        
        DB::transaction(function() use ($personnel) {
            $personnel->update(['statut' => 'actif']);
            $personnel->utilisateur->update(['actif' => true]);
        });

        return redirect()->route('personnel-administration.index')
            ->with('success', 'Personnel d\'administration réactivé avec succès');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(PersonnelAdministration $personnelAdministration)
    {
        $personnelAdministration->utilisateur->update([
            'password' => Hash::make('password123')
        ]);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé. Nouveau mot de passe: password123');
    }

    /**
     * Obtenir toutes les permissions disponibles
     */
    private function getAvailablePermissions()
    {
        return [
            'eleves.view' => 'Voir les élèves',
            'eleves.create' => 'Créer des élèves',
            'eleves.edit' => 'Modifier les élèves',
            'eleves.delete' => 'Supprimer les élèves',
            'enseignants.view' => 'Voir les enseignants',
            'enseignants.create' => 'Créer des enseignants',
            'enseignants.edit' => 'Modifier les enseignants',
            'enseignants.delete' => 'Supprimer les enseignants',
            'classes.view' => 'Voir les classes',
            'classes.create' => 'Créer des classes',
            'classes.edit' => 'Modifier les classes',
            'classes.delete' => 'Supprimer les classes',
            'matieres.view' => 'Voir les matières',
            'matieres.create' => 'Créer des matières',
            'matieres.edit' => 'Modifier les matières',
            'matieres.delete' => 'Supprimer les matières',
            'emplois_temps.view' => 'Voir les emplois du temps',
            'emplois_temps.create' => 'Créer des emplois du temps',
            'emplois_temps.edit' => 'Modifier les emplois du temps',
            'emplois_temps.delete' => 'Supprimer les emplois du temps',
            'absences.view' => 'Voir les absences',
            'absences.create' => 'Saisir des absences',
            'absences.edit' => 'Modifier les absences',
            'absences.delete' => 'Supprimer les absences',
            'notes.view' => 'Voir les notes',
            'notes.create' => 'Saisir des notes',
            'notes.edit' => 'Modifier les notes',
            'notes.delete' => 'Supprimer les notes',
            'paiements.view' => 'Voir les paiements',
            'paiements.create' => 'Enregistrer des paiements',
            'paiements.edit' => 'Modifier les paiements',
            'paiements.delete' => 'Supprimer les paiements',
            'depenses.view' => 'Voir les dépenses',
            'depenses.create' => 'Créer des dépenses',
            'depenses.edit' => 'Modifier les dépenses',
            'depenses.delete' => 'Supprimer les dépenses',
            'statistiques.view' => 'Voir les statistiques',
            'notifications.view' => 'Voir les notifications',
            'notifications.create' => 'Créer des notifications',
            'notifications.edit' => 'Modifier les notifications',
            'notifications.delete' => 'Supprimer les notifications',
            'rapports.view' => 'Voir les rapports',
            'rapports.generate' => 'Générer des rapports',
            'cartes_enseignants.view' => 'Voir les cartes enseignants',
            'cartes_enseignants.create' => 'Créer des cartes enseignants',
            'cartes_enseignants.edit' => 'Modifier les cartes enseignants',
            'cartes_enseignants.delete' => 'Supprimer les cartes enseignants'
        ];
    }
}
