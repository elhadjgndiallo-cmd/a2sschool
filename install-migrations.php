<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "🚀 INSTALLATION AUTOMATIQUE DES MIGRATIONS\n";
echo "==========================================\n\n";

echo "📋 Ce script va:\n";
echo "   1. Détecter les tables existantes\n";
echo "   2. Marquer les migrations correspondantes comme exécutées\n";
echo "   3. Exécuter les migrations manquantes\n";
echo "   4. Vérifier que tout fonctionne\n\n";

// 1. Détecter les tables existantes
echo "🔍 Détection des tables existantes...\n";
$existingTables = [];
$results = DB::select('SHOW TABLES');
foreach ($results as $result) {
    $tableName = array_values((array) $result)[0];
    $existingTables[] = $tableName;
}
echo "✅ " . count($existingTables) . " tables trouvées\n\n";

// 2. Mapping des migrations
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

// 3. Marquer les tables existantes comme migrées
echo "🔄 Marquage des tables existantes comme migrées...\n";
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
            
            echo "✅ Table '$table' marquée comme migrée\n";
            $markedCount++;
        } else {
            echo "ℹ️  Table '$table' déjà marquée comme migrée\n";
        }
    }
}

echo "\n📈 Résumé:\n";
echo "- Tables existantes: " . count($existingTables) . "\n";
echo "- Tables marquées: $markedCount\n\n";

// 4. Exécuter les migrations restantes
echo "🚀 Exécution des migrations restantes...\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo "✅ Migrations exécutées avec succès!\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'exécution des migrations: " . $e->getMessage() . "\n";
}

// 5. Vérification finale
echo "\n🔍 Vérification finale...\n";
try {
    Artisan::call('migrate:status');
    $output = Artisan::output();
    $pendingCount = substr_count($output, 'Pending');
    
    if ($pendingCount == 0) {
        echo "🎉 Toutes les migrations sont à jour!\n";
    } else {
        echo "⚠️  $pendingCount migration(s) en attente\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n🎊 Installation terminée!\n";
echo "Votre système est maintenant prêt à être utilisé.\n";
