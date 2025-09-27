<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Utilisateur;
use App\Models\PersonnelAdministration;

class CheckAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-admin-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie et corrige les permissions de l\'administrateur principal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des Permissions Administrateur');
        $this->info('=============================================');
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
            
            // Vérifier les permissions de base
            $permissions = [
                'evenements.view' => 'Voir les événements',
                'evenements.create' => 'Créer des événements',
                'evenements.edit' => 'Modifier les événements',
                'evenements.delete' => 'Supprimer les événements',
                'notes.view' => 'Voir les notes',
                'notes.create' => 'Créer des notes',
                'notes.edit' => 'Modifier les notes',
                'notes.delete' => 'Supprimer les notes',
                'enseignants.view' => 'Voir les enseignants',
                'enseignants.create' => 'Créer des enseignants',
                'eleves.view' => 'Voir les élèves',
                'eleves.create' => 'Créer des élèves',
                'rapports.view' => 'Voir les rapports',
                'cartes-enseignants.view' => 'Voir les cartes enseignants',
                'cartes-scolaires.view' => 'Voir les cartes scolaires',
                'admin.accounts.view' => 'Gérer les comptes administrateurs'
            ];

            $permissionsOk = 0;
            $permissionsTotal = count($permissions);

            foreach ($permissions as $permission => $description) {
                $hasPermission = $admin->hasPermission($permission);
                if ($hasPermission) {
                    $permissionsOk++;
                    $this->line("   ✅ {$description}");
                } else {
                    $this->error("   ❌ {$description}");
                }
            }

            $this->info("   📊 Permissions: {$permissionsOk}/{$permissionsTotal}");
            
            // Vérifier si l'admin a un profil PersonnelAdministration
            if (!$admin->personnelAdministration) {
                $this->warn("   ⚠️  Pas de profil PersonnelAdministration trouvé");
                
                if ($this->confirm("Voulez-vous créer un profil PersonnelAdministration pour cet administrateur ?")) {
                    $this->createPersonnelAdminProfile($admin);
                }
            } else {
                $this->info("   ✅ Profil PersonnelAdministration trouvé");
            }
            
            $this->newLine();
        }

        $this->info('✅ Vérification terminée!');
        $this->newLine();
        $this->info('📋 Résumé:');
        $this->line('- Tous les administrateurs avec le rôle "admin" ont automatiquement toutes les permissions');
        $this->line('- Si des permissions manquent, vérifiez la méthode hasPermission() dans le modèle Utilisateur');
        $this->line('- Les administrateurs n\'ont pas besoin de profil PersonnelAdministration pour les permissions de base');
    }

    /**
     * Créer un profil PersonnelAdministration pour l'administrateur
     */
    private function createPersonnelAdminProfile($admin)
    {
        try {
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
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors de la création du profil: " . $e->getMessage());
        }
    }
}
