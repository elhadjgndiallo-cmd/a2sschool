<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoMigrate extends Command
{
    protected $signature = 'migrate:auto {--force : Force l\'exécution même en cas de conflit}';
    protected $description = 'Migration automatique qui gère les conflits de tables existantes';

    public function handle()
    {
        $this->info('🚀 MIGRATION AUTOMATIQUE INTELLIGENTE');
        $this->info('=====================================');
        $this->newLine();

        // 1. Vérifier les tables existantes
        $this->info('📊 Vérification des tables existantes...');
        $existingTables = $this->getExistingTables();
        $this->info("✅ " . count($existingTables) . " tables trouvées");
        $this->newLine();

        // 2. Marquer les tables existantes comme migrées
        $this->info('🔄 Marquage des tables existantes comme migrées...');
        $markedCount = $this->markExistingTablesAsMigrated($existingTables);
        $this->newLine();

        // 3. Afficher le résumé
        $this->info('📈 Résumé:');
        $this->line("- Tables existantes: " . count($existingTables));
        $this->line("- Tables marquées: $markedCount");
        $this->newLine();

        // 4. Exécuter les migrations restantes
        $this->info('🚀 Exécution des migrations restantes...');
        $this->call('migrate', ['--force' => $this->option('force')]);
        
        $this->newLine();
        $this->info('🎉 Migration automatique terminée!');
    }
    
    private function getExistingTables(): array
    {
        $tables = [];
        $results = DB::select('SHOW TABLES');
        
        foreach ($results as $result) {
            $tableName = array_values((array) $result)[0];
            $tables[] = $tableName;
        }
        
        return $tables;
    }
    
    private function markExistingTablesAsMigrated(array $existingTables): int
    {
        $migrationMap = [
            'utilisateurs' => '2023_08_15_000000_create_utilisateurs_table',
            'classes' => '2024_08_30_164900_create_classes_table',
            'matieres' => '2024_08_30_164901_create_matieres_table',
            'eleves' => '2024_08_30_164902_create_eleves_table',
            'enseignants' => '2024_08_30_164903_create_enseignants_table',
            'parents' => '2024_08_30_164904_create_parents_table',
            'notes' => '2024_08_30_164906_create_notes_table',
            'absences' => '2024_08_30_164907_create_absences_table',
            'emplois_temps' => '2024_08_30_164908_create_emplois_temps_table',
            'frais_scolarite' => '2024_08_30_164909_create_frais_scolarite_table',
            'paiements' => '2024_08_30_164910_create_paiements_table',
            'depenses' => '2025_09_05_153437_create_depenses_table',
            'salaires_enseignants' => '2025_09_05_193509_create_salaires_enseignants_table',
            'tarifs_classes' => '2025_09_05_202407_create_tarifs_classes_table',
            'etablissements' => '2025_09_07_234742_create_etablissements_table',
            'annee_scolaires' => '2025_09_08_000936_create_annee_scolaires_table',
            'entrees' => '2025_09_11_181400_create_entrees_table',
            'cartes_scolaires' => '2025_09_11_235336_create_cartes_scolaires_table',
            'periodes_scolaires' => '2025_09_13_150553_create_periodes_scolaires_table',
            'cartes_enseignants' => '2025_09_13_211704_create_cartes_enseignants_table',
            'personnel_administration' => '2025_09_15_152713_create_personnel_administration_table',
        ];
        
        $batch = DB::table('migrations')->max('batch') + 1;
        if (!$batch) $batch = 1;
        
        $markedCount = 0;
        foreach ($existingTables as $table) {
            if (isset($migrationMap[$table])) {
                $migration = $migrationMap[$table];
                
                // Vérifier si la migration n'est pas déjà marquée
                $exists = DB::table('migrations')
                    ->where('migration', $migration)
                    ->exists();
                
                if (!$exists) {
                    DB::table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => $batch
                    ]);
                    
                    $this->info("✅ Table '$table' marquée comme migrée");
                    $markedCount++;
                } else {
                    $this->line("ℹ️  Table '$table' déjà marquée comme migrée");
                }
            }
        }
        
        return $markedCount;
    }
}
