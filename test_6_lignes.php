<?php

/**
 * Script de test pour diagnostiquer le problème des 6+ lignes
 * 
 * Usage: php test_6_lignes.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST FACTURATION 6+ LIGNES ===\n\n";

// Simuler 6 lignes de frais
$lignes = [
    [
        'id' => 'scolarite_octobre_2026',
        'libelle' => 'Scolarité Octobre 2026',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
    [
        'id' => 'scolarite_novembre_2026',
        'libelle' => 'Scolarité Novembre 2026',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
    [
        'id' => 'scolarite_decembre_2026',
        'libelle' => 'Scolarité Décembre 2026',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
    [
        'id' => 'scolarite_janvier_2027',
        'libelle' => 'Scolarité Janvier 2027',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
    [
        'id' => 'scolarite_fevrier_2027',
        'libelle' => 'Scolarité Février 2027',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
    [
        'id' => 'scolarite_mars_2027',
        'libelle' => 'Scolarité Mars 2027',
        'montant' => 120000,
        'montant_brut' => 120000,
        'type_frais' => 'scolarite',
    ],
];

echo "Nombre de lignes : " . count($lignes) . "\n";
echo "Total brut : " . array_sum(array_column($lignes, 'montant')) . " GNF\n\n";

$facturationService = app(\App\Services\FacturationService::class);

// Test 1 : Sans remise
echo "--- TEST 1: 6 mois sans remise ---\n";
try {
    $totaux = $facturationService->calculerTotaux($lignes, 'montant', 0);
    echo "✅ Réussi!\n";
    echo "Sous-total : " . $totaux['sous_total'] . " GNF\n";
    echo "Total : " . $totaux['total'] . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC : " . $e->getMessage() . "\n\n";
}

// Test 2 : Avec remise de 50 000 GNF
echo "--- TEST 2: 6 mois avec remise 50000 GNF ---\n";
try {
    $totaux = $facturationService->calculerTotaux($lignes, 'montant', 50000);
    echo "✅ Réussi!\n";
    echo "Sous-total : " . $totaux['sous_total'] . " GNF\n";
    echo "Remise : " . $totaux['montant_remise'] . " GNF\n";
    echo "Total : " . $totaux['total'] . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC : " . $e->getMessage() . "\n\n";
}

// Test 3 : Avec montant versé
echo "--- TEST 3: 6 mois avec remise et montant versé ---\n";
try {
    $totaux = $facturationService->calculerTotauxAvecVersement($lignes, 'montant', 50000, 670000);
    echo "✅ Réussi!\n";
    echo "Sous-total : " . $totaux['sous_total'] . " GNF\n";
    echo "Remise : " . $totaux['montant_remise'] . " GNF\n";
    echo "Total dû : " . $totaux['total_du'] . " GNF\n";
    echo "Montant versé : " . $totaux['montant_verse'] . " GNF\n";
    echo "Reste : " . $totaux['reste_a_payer'] . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n\n";
}

// Test 4 : 5 mois + inscription (le cas qui ne marche pas)
echo "--- TEST 4: 5 mois + inscription avec remise ---\n";
$lignes5Plus = array_slice($lignes, 0, 5);
$lignes5Plus[] = [
    'id' => 'inscription_2026',
    'libelle' => 'Frais d\'inscription 2026-2027',
    'montant' => 30000,
    'montant_brut' => 30000,
    'type_frais' => 'inscription',
];

echo "Nombre de lignes : " . count($lignes5Plus) . "\n";
try {
    $totaux = $facturationService->calculerTotauxAvecVersement($lignes5Plus, 'montant', 50000, 580000);
    echo "✅ Réussi!\n";
    echo "Sous-total : " . $totaux['sous_total'] . " GNF\n";
    echo "Remise : " . $totaux['montant_remise'] . " GNF\n";
    echo "Total dû : " . $totaux['total_du'] . " GNF\n";
    echo "Montant versé : " . $totaux['montant_verse'] . " GNF\n";
    echo "Reste : " . $totaux['reste_a_payer'] . " GNF\n\n";
} catch (\Exception $e) {
    echo "❌ ÉCHEC : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n\n";
}

echo "=== FIN DES TESTS ===\n";
