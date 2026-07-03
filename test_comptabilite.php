<?php

/**
 * Script de test de performance pour le module Comptabilité
 * 
 * Usage: php test_comptabilite.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Entree;
use App\Models\Depense;
use App\Models\SalaireEnseignant;
use App\Models\AnneeScolaire;
use App\Models\Paiement;

echo "═══════════════════════════════════════════════════════════\n";
echo "  TEST DE PERFORMANCE - MODULE COMPTABILITÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Activer le log des requêtes
DB::enableQueryLog();

// Récupérer l'année scolaire active
$anneeScolaire = AnneeScolaire::where('active', true)->first();

if (!$anneeScolaire) {
    echo "❌ Aucune année scolaire active trouvée\n";
    exit(1);
}

echo "Année scolaire : " . $anneeScolaire->annee . "\n";
echo "Période : " . $anneeScolaire->date_debut->format('d/m/Y') . " - " . $anneeScolaire->date_fin->format('d/m/Y') . "\n\n";

// Test 1 : Récupérer les 15 dernières entrées manuelles
echo "Test 1 : 15 dernières entrées manuelles\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$entrees = Entree::with('enregistrePar:id,nom,prenom')
    ->whereBetween('date_entree', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->orderBy('date_entree', 'desc')
    ->limit(15)
    ->get();
$time1 = round((microtime(true) - $start) * 1000, 2);
$queries1 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time1}ms | Requêtes : {$queries1} | Entrées : {$entrees->count()}\n\n";

// Test 2 : Récupérer les 10 dernières dépenses
echo "Test 2 : 10 dernières dépenses\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$depenses = Depense::select([
        'id', 'libelle', 'montant', 'date_depense', 'type_depense', 
        'approuve_par', 'paye_par', 'description'
    ])
    ->with([
        'approuvePar:id,nom,prenom', 
        'payePar:id,nom,prenom'
    ])
    ->whereBetween('date_depense', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->orderBy('date_depense', 'desc')
    ->limit(10)
    ->get();
$time2 = round((microtime(true) - $start) * 1000, 2);
$queries2 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time2}ms | Requêtes : {$queries2} | Dépenses : {$depenses->count()}\n\n";

// Test 3 : Récupérer les 10 derniers salaires
echo "Test 3 : 10 derniers salaires payés\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$salaires = SalaireEnseignant::select([
        'id', 'enseignant_id', 'salaire_net', 'date_paiement', 
        'periode_debut', 'periode_fin', 'paye_par'
    ])
    ->where('statut', 'payé')
    ->whereNotNull('date_paiement')
    ->whereBetween('date_paiement', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->with([
        'enseignant:id,utilisateur_id',
        'enseignant.utilisateur:id,nom,prenom',
        'payePar:id,nom,prenom'
    ])
    ->orderBy('date_paiement', 'desc')
    ->limit(10)
    ->get();
$time3 = round((microtime(true) - $start) * 1000, 2);
$queries3 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time3}ms | Requêtes : {$queries3} | Salaires : {$salaires->count()}\n\n";

// Test 4 : Calculer total des entrées (SUM)
echo "Test 4 : Total des entrées de l'année\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$totalEntrees = Entree::whereBetween('date_entree', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->sum('montant');
$time4 = round((microtime(true) - $start) * 1000, 2);
$queries4 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time4}ms | Requêtes : {$queries4} | Total : " . number_format($totalEntrees, 2) . " FCFA\n\n";

// Test 5 : Calculer total des dépenses (SUM)
echo "Test 5 : Total des dépenses de l'année\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$totalDepenses = Depense::whereBetween('date_depense', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->sum('montant');
$time5 = round((microtime(true) - $start) * 1000, 2);
$queries5 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time5}ms | Requêtes : {$queries5} | Total : " . number_format($totalDepenses, 2) . " FCFA\n\n";

// Test 6 : Calculer total des salaires (SUM)
echo "Test 6 : Total des salaires de l'année\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$start = microtime(true);
$totalSalaires = SalaireEnseignant::where('statut', 'payé')
    ->whereNotNull('date_paiement')
    ->whereBetween('date_paiement', [
        $anneeScolaire->date_debut->format('Y-m-d'),
        $anneeScolaire->date_fin->format('Y-m-d')
    ])
    ->sum('salaire_net');
$time6 = round((microtime(true) - $start) * 1000, 2);
$queries6 = count(DB::getQueryLog());
DB::flushQueryLog();
echo "✓ Temps : {$time6}ms | Requêtes : {$queries6} | Total : " . number_format($totalSalaires, 2) . " FCFA\n\n";

// Résumé
echo "═══════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$totalTime = $time1 + $time2 + $time3 + $time4 + $time5 + $time6;
$totalQueries = $queries1 + $queries2 + $queries3 + $queries4 + $queries5 + $queries6;

echo "Temps total : " . round($totalTime, 2) . "ms\n";
echo "Requêtes totales : {$totalQueries}\n";
echo "Temps moyen par test : " . round($totalTime / 6, 2) . "ms\n\n";

// Statistiques financières
$totalRevenus = $totalEntrees;
$totalSorties = $totalDepenses + $totalSalaires;
$benefice = $totalRevenus - $totalSorties;

echo "Statistiques financières :\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Revenus totaux  : " . number_format($totalRevenus, 2) . " FCFA\n";
echo "Sorties totales : " . number_format($totalSorties, 2) . " FCFA\n";
echo "  • Dépenses    : " . number_format($totalDepenses, 2) . " FCFA\n";
echo "  • Salaires    : " . number_format($totalSalaires, 2) . " FCFA\n";
echo "Bénéfice        : " . number_format($benefice, 2) . " FCFA\n\n";

// Évaluation
if ($totalTime < 200) {
    echo "✅ EXCELLENT - Performance optimale pour la comptabilité !\n";
} elseif ($totalTime < 500) {
    echo "✓ BON - Performance acceptable\n";
} elseif ($totalTime < 1000) {
    echo "⚠ MOYEN - Performance correcte mais peut être améliorée\n";
} else {
    echo "❌ LENT - Optimisation nécessaire\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "💡 RECOMMANDATIONS :\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "• Vider le cache régulièrement : php artisan app:clear-cache\n";
echo "• Les index sont en place pour optimiser les requêtes\n";
echo "• Le cache est actif (3 min pour stats, 10 min pour graphique)\n";
echo "• Dashboard limité à 10 entrées/sorties les plus récentes\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Test terminé le " . date('d/m/Y à H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════\n";
