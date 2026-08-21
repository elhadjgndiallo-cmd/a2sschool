<?php

/**
 * Test de création de facture avec remise
 * Usage: php test_facture_avec_remise.php <eleve_id> <montant_remise>
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST FACTURE AVEC REMISE ===\n\n";

$eleveId = $argv[1] ?? 805;
$montantRemise = $argv[2] ?? 50000;

$eleve = \App\Models\Eleve::with('classe')->find($eleveId);

if (!$eleve) {
    echo "❌ Élève #{$eleveId} non trouvé\n";
    exit(1);
}

echo "Élève: {$eleve->utilisateur->prenom} {$eleve->utilisateur->nom}\n";
echo "Classe: {$eleve->classe->nom}\n\n";

// Récupérer les lignes disponibles
$facturationService = app(\App\Services\FacturationService::class);
$lignesDisponibles = $facturationService->getLignesDisponibles($eleve);

echo "Lignes disponibles: " . count($lignesDisponibles) . "\n\n";

if (count($lignesDisponibles) < 3) {
    echo "⚠️ Pas assez de lignes disponibles pour le test\n";
    exit(1);
}

// Prendre toutes les lignes disponibles
$lignesIds = array_column($lignesDisponibles, 'id');
$sousTotal = array_sum(array_column($lignesDisponibles, 'montant'));

echo "Lignes sélectionnées: " . count($lignesIds) . "\n";
echo "Sous-total: " . number_format($sousTotal, 0, ',', ' ') . " GNF\n";
echo "Remise demandée: " . number_format($montantRemise, 0, ',', ' ') . " GNF\n\n";

// Test 1 : Calcul des totaux SANS remise
echo "--- TEST 1: Calcul totaux SANS remise ---\n";
try {
    $totaux = $facturationService->calculerTotaux($lignesDisponibles, 'montant', 0);
    echo "✅ Sous-total: " . number_format($totaux['sous_total'], 0, ',', ' ') . " GNF\n";
    echo "✅ Remise: " . number_format($totaux['montant_remise'], 0, ',', ' ') . " GNF\n";
    echo "✅ Total: " . number_format($totaux['total'], 0, ',', ' ') . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ERREUR: {$e->getMessage()}\n";
    echo "   Fichier: {$e->getFile()}:{$e->getLine()}\n\n";
}

// Test 2 : Calcul des totaux AVEC remise
echo "--- TEST 2: Calcul totaux AVEC remise ---\n";
try {
    $totaux = $facturationService->calculerTotaux($lignesDisponibles, 'montant', $montantRemise);
    echo "✅ Sous-total: " . number_format($totaux['sous_total'], 0, ',', ' ') . " GNF\n";
    echo "✅ Remise: " . number_format($totaux['montant_remise'], 0, ',', ' ') . " GNF\n";
    echo "✅ Total: " . number_format($totaux['total'], 0, ',', ' ') . " GNF\n";
    echo "✅ Nombre de lignes retournées: " . count($totaux['lignes']) . "\n\n";
} catch (\Exception $e) {
    echo "❌ ERREUR: {$e->getMessage()}\n";
    echo "   Fichier: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}

// Test 3 : Calcul avec versement
echo "--- TEST 3: Calcul avec versement ---\n";
$montantVerse = $sousTotal - $montantRemise;
try {
    $totaux = $facturationService->calculerTotauxAvecVersement(
        $lignesDisponibles,
        'montant',
        $montantRemise,
        $montantVerse
    );
    echo "✅ Sous-total: " . number_format($totaux['sous_total'], 0, ',', ' ') . " GNF\n";
    echo "✅ Remise: " . number_format($totaux['montant_remise'], 0, ',', ' ') . " GNF\n";
    echo "✅ Total dû: " . number_format($totaux['total_du'], 0, ',', ' ') . " GNF\n";
    echo "✅ Montant versé: " . number_format($totaux['montant_verse'], 0, ',', ' ') . " GNF\n";
    echo "✅ Reste: " . number_format($totaux['reste_a_payer'], 0, ',', ' ') . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ERREUR: {$e->getMessage()}\n";
    echo "   Fichier: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}

// Test 4 : Création de la facture
echo "--- TEST 4: Création de la facture ---\n";

$admin = \App\Models\Utilisateur::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun admin trouvé\n";
    exit(1);
}

auth()->login($admin);

$data = [
    'eleve_id' => $eleve->id,
    'mode' => 'mois',
    'lignes' => $lignesIds,
    'date_facture' => date('Y-m-d'),
    'date_echeance' => null,
    'remise_type' => 'montant',
    'remise_valeur' => $montantRemise,
    'montant_verse' => $montantVerse,
    'mode_paiement' => 'especes',
    'reference_paiement' => 'TEST_REMISE_' . time(),
    'observations' => 'Test automatique facture avec remise',
];

echo "Données:\n";
echo "  Lignes: " . count($data['lignes']) . "\n";
echo "  Sous-total: " . number_format($sousTotal, 0, ',', ' ') . " GNF\n";
echo "  Remise: " . number_format($montantRemise, 0, ',', ' ') . " GNF\n";
echo "  Montant versé: " . number_format($montantVerse, 0, ',', ' ') . " GNF\n\n";

try {
    $facture = $facturationService->emettreFacture($data);
    
    echo "✅ SUCCÈS !\n";
    echo "Facture #{$facture->numero_facture} créée\n";
    echo "Sous-total: " . number_format($facture->sous_total, 0, ',', ' ') . " GNF\n";
    echo "Remise: " . number_format($facture->montant_remise, 0, ',', ' ') . " GNF\n";
    echo "Total payé: " . number_format($facture->total, 0, ',', ' ') . " GNF\n";
    echo "Statut: {$facture->statut}\n";
    echo "Lignes: " . $facture->lignes->count() . "\n\n";
    
} catch (\Exception $e) {
    echo "❌ ÉCHEC !\n";
    echo "Erreur: {$e->getMessage()}\n";
    echo "Fichier: {$e->getFile()}:{$e->getLine()}\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
