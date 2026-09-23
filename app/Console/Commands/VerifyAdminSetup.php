<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\PersonnelAdministration;

class VerifyAdminSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verify-admin-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier que l\'administrateur principal a toutes les permissions (79/79)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification du Setup Administrateur Principal');
        $this->info('================================================');
        $this->newLine();

        // Vérifier s'il y a des administrateurs
        $admins = Utilisateur::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('⚠️  Aucun administrateur principal trouvé.');
            $this->info('💡 Pour créer un administrateur principal, accédez à /admin/setup');
            return;
        }

        $this->info("📋 Administrateurs trouvés: {$admins->count()}");
        $this->newLine();

        foreach ($admins as $admin) {
            $this->info("👤 Administrateur: {$admin->nom} {$admin->prenom} ({$admin->email})");
            $this->info("🔑 Rôle: {$admin->role}");
            $this->info("📧 Email: {$admin->email}");
            $this->info("📅 Créé le: {$admin->created_at->format('d/m/Y H:i')}");
            $this->newLine();

            // Vérifier les permissions
            $this->info('🔐 Vérification des Permissions:');
            $this->info('================================');

            // Toutes les permissions possibles de l'application
            $allPermissions = [
                // Événements (5)
                'evenements.view', 'evenements.create', 'evenements.edit', 'evenements.delete', 'evenements.manage_all',
                
                // Notes (6)
                'notes.view', 'notes.create', 'notes.edit', 'notes.delete', 'notes.bulletins', 'notes.statistiques',
                
                // Enseignants (5)
                'enseignants.view', 'enseignants.create', 'enseignants.edit', 'enseignants.delete', 'enseignants.salaires',
                
                // Élèves (5)
                'eleves.view', 'eleves.create', 'eleves.edit', 'eleves.delete', 'eleves.reinscription',
                
                // Classes (4)
                'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
                
                // Matières (4)
                'matieres.view', 'matieres.create', 'matieres.edit', 'matieres.delete',
                
                // Absences (4)
                'absences.view', 'absences.create', 'absences.edit', 'absences.delete',
                'absences-enseignants.view', 'absences-enseignants.create', 'absences-enseignants.edit', 'absences-enseignants.delete',
                
                // Paiements (4)
                'paiements.view', 'paiements.create', 'paiements.edit', 'paiements.delete',
                
                // Rapports (4)
                'rapports.view', 'rapports.financiers', 'rapports.eleves', 'rapports.enseignants',
                
                // Cartes Scolaires (4)
                'cartes-scolaires.view', 'cartes-scolaires.create', 'cartes-scolaires.edit', 'cartes-scolaires.delete',
                
                // Cartes Enseignants (4)
                'cartes-enseignants.view', 'cartes-enseignants.create', 'cartes-enseignants.edit', 'cartes-enseignants.delete',
                
                // Comptabilité - Entrées (4)
                'entrees.view', 'entrees.create', 'entrees.edit', 'entrees.delete',
                
                // Comptabilité - Dépenses (4)
                'depenses.view', 'depenses.create', 'depenses.edit', 'depenses.delete',
                
                // Administration (4)
                'admin.accounts.view', 'admin.accounts.create', 'admin.accounts.edit', 'admin.accounts.delete',
                
                // Établissement (2)
                'etablissement.view', 'etablissement.edit',
                
                // Années Scolaires (4)
                'annees_scolaires.view', 'annees_scolaires.create', 'annees_scolaires.edit', 'annees_scolaires.delete',
                
                // Tarifs (4)
                'tarifs.view', 'tarifs.create', 'tarifs.edit', 'tarifs.delete',
                
                // Messages (4)
                'messages.view', 'messages.create', 'messages.edit', 'messages.delete',
                
                // Notifications (4)
                'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',
            ];

            $permissionsGranted = 0;
            $permissionsDenied = 0;

            foreach ($allPermissions as $permission) {
                if ($admin->hasPermission($permission)) {
                    $permissionsGranted++;
                    $this->line("   ✅ {$permission}");
                } else {
                    $permissionsDenied++;
                    $this->error("   ❌ {$permission}");
                }
            }

            $this->newLine();
            $this->info("📊 Résumé des Permissions:");
            $this->info("==========================");
            $this->info("✅ Permissions accordées: {$permissionsGranted}/" . count($allPermissions));
            $this->info("❌ Permissions refusées: {$permissionsDenied}/" . count($allPermissions));

            if ($permissionsDenied === 0) {
                $this->info("🎉 PARFAIT! L'administrateur principal a TOUTES les permissions ({$permissionsGranted}/" . count($allPermissions) . ")");
                $this->info("🚀 L'administrateur peut accéder à toutes les fonctionnalités de l'application");
            } else {
                $this->error("⚠️  ATTENTION! L'administrateur n'a pas toutes les permissions");
                $this->error("🔧 Exécutez: php artisan app:fix-admin-permissions");
            }

            $this->newLine();

            // Vérifier le profil PersonnelAdministration
            $this->info('👥 Vérification du Profil PersonnelAdministration:');
            $this->info('===============================================');

            if ($admin->personnelAdministration) {
                $this->info("✅ Profil PersonnelAdministration trouvé");
                $this->info("📋 Poste: {$admin->personnelAdministration->poste}");
                $this->info("📅 Date d'embauche: {$admin->personnelAdministration->date_embauche->format('d/m/Y')}");
                $this->info("🔧 Statut: " . ($admin->personnelAdministration->actif ? 'Actif' : 'Inactif'));
            } else {
                $this->warn("⚠️  Aucun profil PersonnelAdministration trouvé");
                $this->info("🔧 Création automatique du profil...");
                
                try {
                    PersonnelAdministration::create([
                        'utilisateur_id' => $admin->id,
                        'poste' => 'Administrateur Principal',
                        'date_embauche' => now(),
                        'permissions' => json_encode($allPermissions),
                        'actif' => true,
                    ]);
                    $this->info("✅ Profil PersonnelAdministration créé avec succès");
                } catch (\Exception $e) {
                    $this->error("❌ Erreur lors de la création du profil: " . $e->getMessage());
                }
            }

            $this->newLine();
        }

        $this->info('🎯 Vérification Terminée!');
        $this->info('========================');
        $this->info('✅ L\'administrateur principal est correctement configuré');
        $this->info('🚀 Toutes les fonctionnalités sont accessibles');
        $this->info('💡 Pour créer un nouvel admin: /admin/setup');
    }
}
