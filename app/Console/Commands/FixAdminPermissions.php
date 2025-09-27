<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\PersonnelAdministration;

class FixAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-admin-permissions {--force : Forcer la correction sans demander confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige automatiquement les permissions de tous les administrateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction des Permissions Administrateur');
        $this->info('==========================================');
        $this->newLine();

        // Trouver tous les administrateurs
        $admins = Utilisateur::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('⚠️  Aucun administrateur trouvé dans le système');
            return;
        }

        $this->info("📋 Administrateurs trouvés: " . $admins->count());
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous corriger les permissions de tous les administrateurs ?')) {
                $this->info('Opération annulée');
                return;
            }
        }

        $corrected = 0;
        $errors = 0;

        foreach ($admins as $admin) {
            $this->info("👤 Traitement de: {$admin->nom} {$admin->prenom} ({$admin->email})");
            
            try {
                // Vérifier si l'admin a un profil PersonnelAdministration
                if (!$admin->personnelAdministration) {
                    $this->line("   🔧 Création du profil PersonnelAdministration...");
                    
                    PersonnelAdministration::create([
                        'utilisateur_id' => $admin->id,
                        'poste' => 'Administrateur Principal',
                        'date_embauche' => now(),
                        'permissions' => json_encode([
                            'evenements.view', 'evenements.create', 'evenements.edit', 'evenements.delete', 'evenements.manage_all',
                            'notes.view', 'notes.create', 'notes.edit', 'notes.delete',
                            'enseignants.view', 'enseignants.create', 'enseignants.edit', 'enseignants.delete',
                            'eleves.view', 'eleves.create', 'eleves.edit', 'eleves.delete',
                            'rapports.view', 'cartes-enseignants.view', 'cartes-scolaires.view',
                            'admin.accounts.view', 'admin.accounts.create', 'admin.accounts.edit', 'admin.accounts.delete'
                        ]),
                        'actif' => true,
                    ]);
                    
                    $this->info("   ✅ Profil PersonnelAdministration créé avec succès");
                    $corrected++;
                } else {
                    $this->info("   ✅ Profil PersonnelAdministration déjà existant");
                }
                
                // Vérifier les permissions
                $permissions = [
                    'evenements.view', 'evenements.create', 'evenements.edit', 'evenements.delete',
                    'notes.view', 'notes.create', 'notes.edit', 'notes.delete',
                    'enseignants.view', 'enseignants.create', 'eleves.view', 'eleves.create',
                    'rapports.view', 'cartes-enseignants.view', 'cartes-scolaires.view',
                    'admin.accounts.view'
                ];
                
                $permissionsOk = 0;
                foreach ($permissions as $permission) {
                    if ($admin->hasPermission($permission)) {
                        $permissionsOk++;
                    }
                }
                
                $this->info("   📊 Permissions: {$permissionsOk}/" . count($permissions));
                
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur: " . $e->getMessage());
                $errors++;
            }
            
            $this->newLine();
        }

        $this->info('✅ Correction terminée!');
        $this->newLine();
        $this->info("📊 Résumé:");
        $this->line("- Administrateurs traités: " . $admins->count());
        $this->line("- Profils créés: " . $corrected);
        $this->line("- Erreurs: " . $errors);
        $this->newLine();
        $this->info("🎯 Tous les administrateurs ont maintenant toutes les permissions nécessaires!");
    }
}
