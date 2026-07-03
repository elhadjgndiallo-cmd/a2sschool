<?php

/**
 * Script de test de performance des requêtes
 * 
 * Usage: php test_performance.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Eleve;
use App\Models\Note;
use App\Models\Absence;
use App\Models\AnneeScolaire;

echo "═══════════════════════════════════════════════════════════\n";
echo "  TEST DE PERFORMANCE - A2S SCHOOL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Activer le log des requêtes
DB::enableQueryLog();

// Test 1 : Récupérer l'année scolaire active
echo "Test 1 : Année scolaire active\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$annee = AnneeScolaire::where('active', true)->first();
$time1 = round((microtime(true) - $start) * 1000, 2);
$queries1 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time1}ms | Requêtes : {$queries1}\n\n";

// Test 2 : Liste des élèves avec relations
echo "Test 2 : Liste 20 élèves avec relations\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$eleves = Eleve::with(['utilisateur', 'classe'])
    ->where('actif', true)
    ->limit(20)
    ->get();
$time2 = round((microtime(true) - $start) * 1000, 2);
$queries2 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time2}ms | Requêtes : {$queries2} | Élèves : {$eleves->count()}\n\n";

// Test 3 : Compter les notes d'un trimestre
echo "Test 3 : Compter les notes\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$totalNotes = Note::count();
$time3 = round((microtime(true) - $start) * 1000, 2);
$queries3 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time3}ms | Requêtes : {$queries3} | Total : {$totalNotes}\n\n";

// Test 4 : Compter les absences
echo "Test 4 : Compter les absences\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$totalAbsences = Absence::count();
$time4 = round((microtime(true) - $start) * 1000, 2);
$queries4 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time4}ms | Requêtes : {$queries4} | Total : {$totalAbsences}\n\n";

// Test 5 : Statistiques élèves par classe
echo "Test 5 : Élèves par classe (avec index)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$statsClasses = DB::table('eleves')
    ->select('classe_id', DB::raw('COUNT(*) as total'))
    ->where('actif', true)
    ->groupBy('classe_id')
    ->get();
$time5 = round((microtime(true) - $start) * 1000, 2);
$queries5 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time5}ms | Requêtes : {$queries5} | Classes : {$statsClasses->count()}\n\n";

// Résumé
echo "═══════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$totalTime = $time1 + $time2 + $time3 + $time4 + $time5;
$totalQueries = $queries1 + $queries2 + $queries3 + $queries4 + $queries5;

echo "Temps total : " . round($totalTime, 2) . "ms\n";
echo "Requêtes totales : {$totalQueries}\n";
echo "Temps moyen par test : " . round($totalTime / 5, 2) . "ms\n\n";

// Évaluation
if ($totalTime < 500) {
    echo "✅ EXCELLENT - Performance optimale !\n";
} elseif ($totalTime < 1000) {
    echo "✓ BON - Performance acceptable\n";
} elseif ($totalTime < 2000) {
    echo "⚠ MOYEN - Performance correcte mais peut être améliorée\n";
} else {
    echo "❌ LENT - Optimisation nécessaire\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Test terminé le " . date('d/m/Y à H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════\n";
