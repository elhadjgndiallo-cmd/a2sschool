<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Helpers\PermissionHelper;

class CheckAllPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-all-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie toutes les permissions possibles de l\'application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification Complète des Permissions');
        $this->info('========================================');
        $this->newLine();

        // Trouver tous les administrateurs
        $admins = Utilisateur::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('⚠️  Aucun administrateur trouvé dans le système');
            return;
        }

        $this->info("📋 Administrateurs trouvés: " . $admins->count());
        $this->newLine();

        foreach ($admins as $admin) {
            $this->info("👤 Administrateur: {$admin->nom} {$admin->prenom} ({$admin->email})");
            $this->info("🔑 Rôle: {$admin->role}");
            $this->newLine();

            // Toutes les permissions possibles de l'application
            $allPermissions = [
                // Événements
                'evenements.view' => 'Voir les événements',
                'evenements.create' => 'Créer des événements',
                'evenements.edit' => 'Modifier les événements',
                'evenements.delete' => 'Supprimer les événements',
                'evenements.manage_all' => 'Gérer tous les événements',
                
                // Notes
                'notes.view' => 'Voir les notes',
                'notes.create' => 'Créer des notes',
                'notes.edit' => 'Modifier les notes',
                'notes.delete' => 'Supprimer les notes',
                'notes.bulletins' => 'Générer les bulletins',
                'notes.statistiques' => 'Voir les statistiques des notes',
                
                // Enseignants
                'enseignants.view' => 'Voir les enseignants',
                'enseignants.create' => 'Créer des enseignants',
                'enseignants.edit' => 'Modifier les enseignants',
                'enseignants.delete' => 'Supprimer les enseignants',
                'enseignants.salaires' => 'Gérer les salaires des enseignants',
                
                // Élèves
                'eleves.view' => 'Voir les élèves',
                'eleves.create' => 'Créer des élèves',
                'eleves.edit' => 'Modifier les élèves',
                'eleves.delete' => 'Supprimer les élèves',
                'eleves.reinscription' => 'Gérer les réinscriptions',
                
                // Classes
                'classes.view' => 'Voir les classes',
                'classes.create' => 'Créer des classes',
                'classes.edit' => 'Modifier les classes',
                'classes.delete' => 'Supprimer les classes',
                
                // Matières
                'matieres.view' => 'Voir les matières',
                'matieres.create' => 'Créer des matières',
                'matieres.edit' => 'Modifier les matières',
                'matieres.delete' => 'Supprimer les matières',
                
                // Absences
                'absences.view' => 'Voir les absences',
                'absences.create' => 'Créer des absences',
                'absences.edit' => 'Modifier les absences',
                'absences.delete' => 'Supprimer les absences',
                
                // Paiements
                'paiements.view' => 'Voir les paiements',
                'paiements.create' => 'Créer des paiements',
                'paiements.edit' => 'Modifier les paiements',
                'paiements.delete' => 'Supprimer les paiements',
                
                // Rapports
                'rapports.view' => 'Voir les rapports',
                'rapports.financiers' => 'Rapports financiers',
                'rapports.eleves' => 'Rapports élèves',
                'rapports.enseignants' => 'Rapports enseignants',
                
                // Cartes
                'cartes-scolaires.view' => 'Voir les cartes scolaires',
                'cartes-scolaires.create' => 'Créer des cartes scolaires',
                'cartes-scolaires.edit' => 'Modifier les cartes scolaires',
                'cartes-scolaires.delete' => 'Supprimer les cartes scolaires',
                'cartes-enseignants.view' => 'Voir les cartes enseignants',
                'cartes-enseignants.create' => 'Créer des cartes enseignants',
                'cartes-enseignants.edit' => 'Modifier les cartes enseignants',
                'cartes-enseignants.delete' => 'Supprimer les cartes enseignants',
                
                // Comptabilité
                'entrees.view' => 'Voir les entrées',
                'entrees.create' => 'Créer des entrées',
                'entrees.edit' => 'Modifier les entrées',
                'entrees.delete' => 'Supprimer les entrées',
                'depenses.view' => 'Voir les dépenses',
                'depenses.create' => 'Créer des dépenses',
                'depenses.edit' => 'Modifier les dépenses',
                'depenses.delete' => 'Supprimer les dépenses',
                
                // Administration
                'admin.accounts.view' => 'Voir les comptes administrateurs',
                'admin.accounts.create' => 'Créer des comptes administrateurs',
                'admin.accounts.edit' => 'Modifier les comptes administrateurs',
                'admin.accounts.delete' => 'Supprimer les comptes administrateurs',
                
                // Établissement
                'etablissement.view' => 'Voir les informations de l\'établissement',
                'etablissement.edit' => 'Modifier les informations de l\'établissement',
                
                // Années scolaires
                'annees_scolaires.view' => 'Voir les années scolaires',
                'annees_scolaires.create' => 'Créer des années scolaires',
                'annees_scolaires.edit' => 'Modifier les années scolaires',
                'annees_scolaires.delete' => 'Supprimer les années scolaires',
                
                // Tarifs
                'tarifs.view' => 'Voir les tarifs',
                'tarifs.create' => 'Créer des tarifs',
                'tarifs.edit' => 'Modifier les tarifs',
                'tarifs.delete' => 'Supprimer les tarifs',
                
                // Messages
                'messages.view' => 'Voir les messages',
                'messages.create' => 'Créer des messages',
                'messages.edit' => 'Modifier les messages',
                'messages.delete' => 'Supprimer les messages',
                
                // Notifications
                'notifications.view' => 'Voir les notifications',
                'notifications.create' => 'Créer des notifications',
                'notifications.edit' => 'Modifier les notifications',
                'notifications.delete' => 'Supprimer les notifications'
            ];

            $permissionsOk = 0;
            $permissionsTotal = count($allPermissions);
            $missingPermissions = [];

            $this->info("📋 Vérification de {$permissionsTotal} permissions:");
            $this->newLine();

            foreach ($allPermissions as $permission => $description) {
                $hasPermission = $admin->hasPermission($permission);
                if ($hasPermission) {
                    $permissionsOk++;
                    $this->line("   ✅ {$description}");
                } else {
                    $missingPermissions[] = $permission;
                    $this->error("   ❌ {$description}");
                }
            }

            $this->newLine();
            $this->info("📊 Résumé des permissions: {$permissionsOk}/{$permissionsTotal}");

            if (!empty($missingPermissions)) {
                $this->warn("⚠️  Permissions manquantes: " . implode(', ', $missingPermissions));
            } else {
                $this->info("✅ Toutes les permissions sont accordées!");
            }

            // Vérifier les sous-menus
            $this->newLine();
            $this->info("📋 Vérification des sous-menus:");
            
            $menuTypes = ['enseignants', 'eleves', 'notes', 'comptabilite', 'statistiques', 'cartes', 'parametres'];
            
            foreach ($menuTypes as $menuType) {
                try {
                    $submenus = PermissionHelper::getFilteredSubmenus($menuType);
                    $this->line("   🔸 {$menuType}: " . count($submenus) . " sous-menu(s)");
                } catch (\Exception $e) {
                    $this->error("   ❌ {$menuType}: Erreur - " . $e->getMessage());
                }
            }
            
            $this->newLine();
        }

        $this->info('✅ Vérification terminée!');
        $this->newLine();
        $this->info('📋 Résumé:');
        $this->line('- Vérification complète de toutes les permissions de l\'application');
        $this->line('- Test des sous-menus pour chaque type de menu');
        $this->line('- Identification des permissions manquantes si elles existent');
    }
}
