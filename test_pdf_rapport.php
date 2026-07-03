<?php

/**
 * Script de test pour le PDF du rapport comptabilité
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════════════════════════\n";
echo "  TEST PDF RAPPORT COMPTABILITÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Simuler une requête
$request = new Illuminate\Http\Request([
    'type' => 'annee',
    'format' => 'pdf'
]);

// Créer une instance du contrôleur
$controller = new App\Http\Controllers\ComptabiliteController();

echo "Test 1 : Récupération année scolaire active\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();

if ($anneeScolaire) {
    echo "✓ Année scolaire trouvée : " . $anneeScolaire->nom . "\n";
    echo "  Période : " . $anneeScolaire->date_debut->format('d/m/Y') . " - " . $anneeScolaire->date_fin->format('d/m/Y') . "\n";
} else {
    echo "✗ Aucune année scolaire active\n";
    exit(1);
}

echo "\nTest 2 : Vérification vue PDF\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$viewPath = resource_path('views/comptabilite/rapport-journalier-pdf.blade.php');
if (file_exists($viewPath)) {
    echo "✓ Vue PDF existe : $viewPath\n";
} else {
    echo "✗ Vue PDF introuvable\n";
    exit(1);
}

echo "\nTest 3 : Test génération données rapport\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $dateDebut = \Carbon\Carbon::parse($anneeScolaire->date_debut)->startOfDay();
    $dateFin = \Carbon\Carbon::parse($anneeScolaire->date_fin)->endOfDay();
    
    echo "✓ Période de rapport définie\n";
    echo "  Début : " . $dateDebut->format('d/m/Y') . "\n";
    echo "  Fin : " . $dateFin->format('d/m/Y') . "\n";
    
    // Test comptage des entrées
    $nbEntrees = \App\Models\Entree::whereBetween('date_entree', [
        $dateDebut->format('Y-m-d'),
        $dateFin->format('Y-m-d')
    ])->count();
    
    echo "  Entrées : $nbEntrees\n";
    
    // Test comptage des dépenses
    $nbDepenses = \App\Models\Depense::whereBetween('date_depense', [
        $dateDebut->format('Y-m-d'),
        $dateFin->format('Y-m-d')
    ])->count();
    
    echo "  Dépenses : $nbDepenses\n";
    
    // Test comptage des salaires
    $nbSalaires = \App\Models\SalaireEnseignant::where('statut', 'payé')
        ->whereNotNull('date_paiement')
        ->whereBetween('date_paiement', [
            $dateDebut->format('Y-m-d'),
            $dateFin->format('Y-m-d')
        ])->count();
    
    echo "  Salaires : $nbSalaires\n";
    
    $totalTransactions = $nbEntrees + $nbDepenses + $nbSalaires;
    echo "  Total transactions : $totalTransactions\n";
    
    if ($totalTransactions > 0) {
        echo "✓ Données disponibles pour le rapport\n";
    } else {
        echo "⚠ Aucune transaction dans cette période\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTest 4 : Vérification package PDF\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
    echo "✓ Package dompdf chargé\n";
} else {
    echo "✗ Package dompdf introuvable\n";
    exit(1);
}

echo "\nTest 5 : URL de téléchargement\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$url = route('comptabilite.rapport-journalier', [
    'type' => 'annee',
    'format' => 'pdf'
]);

echo "URL : $url\n";
echo "✓ Route générée correctement\n";

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "✅ Tous les prérequis sont OK\n";
echo "✅ Le PDF devrait se générer correctement\n\n";

echo "💡 Pour tester :\n";
echo "   1. Ouvrir : $url\n";
echo "   2. Le PDF devrait se télécharger\n";
echo "   3. Si erreur, vérifier storage/logs/laravel.log\n\n";

echo "═══════════════════════════════════════════════════════════\n";
