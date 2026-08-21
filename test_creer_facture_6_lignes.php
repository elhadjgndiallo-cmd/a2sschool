<?php

/**
 * Script de test pour créer une facture avec 6 lignes directement
 * 
 * Usage: php test_creer_facture_6_lignes.php <eleve_id>
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST CRÉATION FACTURE 6 LIGNES ===\n\n";

$eleveId = $argv[1] ?? null;

if (!$eleveId) {
    echo "❌ Usage: php test_creer_facture_6_lignes.php <eleve_id>\n";
    echo "Exemple: php test_creer_facture_6_lignes.php 805\n";
    exit(1);
}

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

echo "Lignes disponibles: " . count($lignesDisponibles) . "\n";

if (count($lignesDisponibles) < 6) {
    echo "⚠️  Cet élève n'a que " . count($lignesDisponibles) . " lignes disponibles (besoin de 6 minimum)\n";
    echo "Listant les lignes disponibles:\n";
    foreach ($lignesDisponibles as $i => $ligne) {
        echo "  " . ($i + 1) . ". {$ligne['id']} - {$ligne['libelle']} - {$ligne['montant']} GNF\n";
    }
    exit(1);
}

// Prendre les 6 premières lignes
$lignesIds = array_slice(array_column($lignesDisponibles, 'id'), 0, 6);

echo "Lignes sélectionnées pour la facture:\n";
foreach ($lignesIds as $i => $id) {
    $ligne = collect($lignesDisponibles)->firstWhere('id', $id);
    echo "  " . ($i + 1) . ". {$ligne['libelle']} - {$ligne['montant']} GNF\n";
}
echo "\n";

// Simuler un utilisateur admin
$admin = \App\Models\User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun admin trouvé\n";
    exit(1);
}

auth()->login($admin);
echo "Connecté en tant que: {$admin->email}\n\n";

// Données de la facture
$data = [
    'eleve_id' => $eleve->id,
    'mode' => 'mois',
    'lignes' => $lignesIds,
    'date_facture' => date('Y-m-d'),
    'date_echeance' => null,
    'remise_type' => 'montant',
    'remise_valeur' => 0,
    'montant_verse' => array_sum(array_column(array_slice($lignesDisponibles, 0, 6), 'montant')),
    'mode_paiement' => 'especes',
    'reference_paiement' => 'TEST_6_LIGNES_' . time(),
    'observations' => 'Test automatique création facture 6 lignes',
];

echo "Données de la facture:\n";
echo "  Nombre de lignes: " . count($data['lignes']) . "\n";
echo "  Montant total: " . $data['montant_verse'] . " GNF\n";
echo "  Remise: 0 GNF\n\n";

echo "Tentative de création...\n";

try {
    $facture = $facturationService->emettreFacture($data);
    
    echo "✅ SUCCÈS !\n";
    echo "Facture #{$facture->numero_facture} créée\n";
    echo "ID: {$facture->id}\n";
    echo "Sous-total: {$facture->sous_total} GNF\n";
    echo "Total: {$facture->total} GNF\n";
    echo "Statut: {$facture->statut}\n";
    echo "Nombre de lignes: " . $facture->lignes->count() . "\n\n";
    
    echo "Détails des lignes:\n";
    foreach ($facture->lignes as $i => $ligne) {
        echo "  " . ($i + 1) . ". {$ligne->libelle} - {$ligne->montant} GNF\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ÉCHEC !\n";
    echo "Erreur: {$e->getMessage()}\n";
    echo "Fichier: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString();
}

echo "\n=== FIN DU TEST ===\n";
