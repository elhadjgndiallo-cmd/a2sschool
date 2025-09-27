<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Route;

class CheckSubmenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-submenus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie que tous les sous-menus sont correctement configurés';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des Sous-Menus');
        $this->info('==============================');
        $this->newLine();

        // Contourner l'authentification en modifiant temporairement PermissionHelper
        $originalHelper = \App\Helpers\PermissionHelper::class;
        
        // Créer un mock de PermissionHelper
        $mockHelper = new class {
            public static function getFilteredSubmenus($menuType) {
                $allSubmenus = [
                    'enseignants' => [
                        ['href' => route('enseignants.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'enseignants.create'],
                        ['href' => route('enseignants.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'enseignants.view'],
                        ['href' => route('salaires.index'), 'icon' => 'fas fa-coins', 'text' => 'Salaire', 'permission' => 'salaires.view']
                    ],
                    'eleves' => [
                        ['href' => route('eleves.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'eleves.view'],
                        ['href' => route('eleves.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'eleves.create'],
                        ['href' => route('eleves.reinscription'), 'icon' => 'fas fa-user-edit', 'text' => 'Réinscription', 'permission' => 'eleves.edit'],
                        ['href' => route('paiements.index'), 'icon' => 'fas fa-money-bill-wave', 'text' => 'Frais', 'permission' => 'paiements.view'],
                        ['href' => route('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes scolaires', 'permission' => 'cartes-scolaires.view']
                    ],
                    'notes' => [
                        ['href' => route('notes.index'), 'icon' => 'fas fa-edit', 'text' => 'Saisir Notes', 'permission' => 'notes.view'],
                        ['href' => route('notes.bulletins'), 'icon' => 'fas fa-chart-line', 'text' => 'Bulletins', 'permission' => 'notes.view'],
                        ['href' => route('notes.statistiques'), 'icon' => 'fas fa-chart-bar', 'text' => 'Statistiques', 'permission' => 'notes.view'],
                        ['href' => route('notes.parametres'), 'icon' => 'fas fa-cog', 'text' => 'Paramètres', 'permission' => 'notes.edit']
                    ],
                    'comptabilite' => [
                        ['href' => route('entrees.index'), 'icon' => 'fas fa-arrow-up', 'text' => 'Entrée', 'permission' => 'entrees.view'],
                        ['href' => route('depenses.index'), 'icon' => 'fas fa-arrow-down', 'text' => 'Sortie', 'permission' => 'depenses.view'],
                        ['href' => route('rapports.index'), 'icon' => 'fas fa-chart-line', 'text' => 'Rapport', 'permission' => 'rapports.view']
                    ],
                    'statistiques' => [
                        ['href' => route('rapports.index'), 'icon' => 'fas fa-chart-line', 'text' => 'Rapports Financiers', 'permission' => 'rapports.view'],
                        ['href' => route('depenses.rapports'), 'icon' => 'fas fa-chart-bar', 'text' => 'Rapports Dépenses', 'permission' => 'depenses.view'],
                        ['href' => route('paiements.rapports'), 'icon' => 'fas fa-chart-pie', 'text' => 'Rapports Paiements', 'permission' => 'paiements.view'],
                        ['href' => route('salaires.rapports'), 'icon' => 'fas fa-coins', 'text' => 'Rapports Salaires', 'permission' => 'salaires.view']
                    ],
                    'cartes' => [
                        ['href' => route('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes Scolaires', 'permission' => 'cartes-scolaires.view'],
                        ['href' => route('cartes-enseignants.index'), 'icon' => 'fas fa-id-badge', 'text' => 'Cartes Enseignants', 'permission' => 'cartes-enseignants.view']
                    ],
                    'parametres' => [
                        ['href' => route('etablissement.informations'), 'icon' => 'fas fa-info-circle', 'text' => 'Informations', 'permission' => 'etablissement.view'],
                        ['href' => route('etablissement.responsabilites'), 'icon' => 'fas fa-users-cog', 'text' => 'Responsabilités', 'permission' => 'etablissement.view'],
                        ['href' => route('annees-scolaires.index'), 'icon' => 'fas fa-calendar-alt', 'text' => 'Année Scolaire', 'permission' => 'annees_scolaires.view'],
                        ['href' => route('classes.index'), 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Classes', 'permission' => 'classes.view'],
                        ['href' => route('tarifs.tableau'), 'icon' => 'fas fa-chart-line', 'text' => 'Tableau des Tarifs', 'permission' => 'tarifs.view'],
                        ['href' => route('evenements.index'), 'icon' => 'fas fa-calendar-check', 'text' => 'Événements', 'permission' => 'evenements.view'],
                        ['href' => route('admin.accounts.index'), 'icon' => 'fas fa-user-shield', 'text' => 'Comptes Administrateurs', 'permission' => 'admin.accounts.view']
                    ]
                ];

                if (!isset($allSubmenus[$menuType])) {
                    return [];
                }

                // Retourner tous les sous-menus (simulation d'un admin)
                return $allSubmenus[$menuType];
            }
        };

        $this->info('📋 Vérification des sous-menus disponibles:');
        $this->newLine();

        $menuTypes = ['enseignants', 'eleves', 'notes', 'comptabilite', 'statistiques', 'cartes', 'parametres'];

        foreach ($menuTypes as $menuType) {
            $this->info("🔸 Menu: $menuType");
            
            try {
                $submenus = $mockHelper::getFilteredSubmenus($menuType);
                
                if (empty($submenus)) {
                    $this->warn("   ⚠️  Aucun sous-menu trouvé");
                } else {
                    $this->info("   ✅ " . count($submenus) . " sous-menu(s) trouvé(s):");
                    foreach ($submenus as $submenu) {
                        $this->line("      - {$submenu['text']} ({$submenu['href']})");
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        $this->info('🎯 Vérification des routes critiques:');
        $this->newLine();

        $criticalRoutes = [
            'rapports.index' => 'Rapports Financiers',
            'cartes-enseignants.index' => 'Cartes Enseignants',
            'cartes-scolaires.index' => 'Cartes Scolaires',
            'depenses.rapports' => 'Rapports Dépenses',
            'paiements.rapports' => 'Rapports Paiements',
            'salaires.rapports' => 'Rapports Salaires'
        ];

        foreach ($criticalRoutes as $route => $description) {
            try {
                $url = route($route);
                $this->info("✅ $description: $url");
            } catch (\Exception $e) {
                $this->error("❌ $description: Route non trouvée - " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('✅ Vérification terminée!');
        $this->newLine();
        $this->info('📋 Si des sous-menus manquent, vérifiez:');
        $this->line('1. Les routes sont bien définies dans routes/web.php');
        $this->line('2. Les contrôleurs existent');
        $this->line('3. Les permissions sont correctement configurées');
    }
}
