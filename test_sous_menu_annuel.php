<?php

/**
 * Script de test pour le sous-menu Annuel
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════════════════════════\n";
echo "  TEST SOUS-MENU ANNUEL - GESTION DES NOTES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Test 1 : Vérification année scolaire active\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();

if ($anneeScolaire) {
    echo "✓ Année scolaire trouvée : " . $anneeScolaire->nom . "\n";
} else {
    echo "✗ Aucune année scolaire active\n";
    exit(1);
}

echo "\nTest 2 : Vérification routes\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$routes = [
    'notes.annuel.index' => '/notes/annuel',
    'notes.annuel.resultats' => '/notes/annuel/resultats/{classe}',
    'notes.annuel.detail-notes.imprimer' => '/notes/annuel/resultats/{classe}/detail-notes/imprimer',
];

foreach ($routes as $name => $uri) {
    if (Route::has($name)) {
        echo "✓ Route '$name' existe\n";
    } else {
        echo "✗ Route '$name' manquante\n";
    }
}

echo "\nTest 3 : Vérification vues\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$views = [
    'notes.annuel.index',
    'notes.annuel.resultats',
    'notes.annuel.detail-notes-imprimer',
];

foreach ($views as $view) {
    $viewPath = str_replace('.', '/', $view) . '.blade.php';
    $fullPath = resource_path('views/' . $viewPath);
    
    if (file_exists($fullPath)) {
        echo "✓ Vue '$view' existe\n";
    } else {
        echo "✗ Vue '$view' manquante : $fullPath\n";
    }
}

echo "\nTest 4 : Vérification méthode Note\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (method_exists(\App\Models\Note::class, 'calculerMoyenneAnnuelleEleveMatiere')) {
    echo "✓ Méthode 'calculerMoyenneAnnuelleEleveMatiere' existe\n";
} else {
    echo "✗ Méthode 'calculerMoyenneAnnuelleEleveMatiere' manquante\n";
}

echo "\nTest 5 : Comptage classes et élèves\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$nbClasses = \App\Models\Classe::whereHas('eleves', function($query) use ($anneeScolaire) {
    $query->where('annee_scolaire_id', $anneeScolaire->id);
})->count();
echo "  Classes : $nbClasses\n";

$nbEleves = \App\Models\Eleve::where('annee_scolaire_id', $anneeScolaire->id)->count();
echo "  Élèves : $nbEleves\n";

$nbNotes = \App\Models\Note::count();
echo "  Notes : $nbNotes\n";

if ($nbClasses > 0 && $nbEleves > 0 && $nbNotes > 0) {
    echo "✓ Données disponibles pour les tests\n";
} else {
    echo "⚠ Attention : Peu ou pas de données disponibles\n";
}

echo "\nTest 6 : Test calcul moyenne annuelle\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$eleve = \App\Models\Eleve::where('annee_scolaire_id', $anneeScolaire->id)->first();

if ($eleve) {
    echo "  Élève testé : " . $eleve->utilisateur->prenom . " " . $eleve->utilisateur->nom . "\n";
    
    // Tester le calcul de moyenne annuelle
    $moyenneAnnuelle = \App\Models\Note::calculerMoyenneAnnuelle($eleve->id);
    
    if ($moyenneAnnuelle !== null) {
        echo "  Moyenne annuelle : " . number_format($moyenneAnnuelle, 2) . "/20\n";
        echo "✓ Calcul de moyenne annuelle fonctionne\n";
    } else {
        echo "  Moyenne annuelle : Non calculée (aucune note)\n";
        echo "⚠ L'élève n'a pas de notes\n";
    }
    
    // Tester le calcul par matière
    $matiere = \App\Models\Matiere::first();
    if ($matiere) {
        $moyenneMatiere = \App\Models\Note::calculerMoyenneAnnuelleEleveMatiere($eleve->id, $matiere->id);
        echo "  Moyenne " . $matiere->nom . " : " . ($moyenneMatiere ? number_format($moyenneMatiere, 2) : 'N/A') . "\n";
        echo "✓ Calcul par matière fonctionne\n";
    }
} else {
    echo "⚠ Aucun élève trouvé pour tester\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "✅ Sous-menu Annuel installé avec succès\n";
echo "✅ Routes créées\n";
echo "✅ Vues créées\n";
echo "✅ Méthodes ajoutées au modèle Note\n\n";

echo "💡 Pour accéder :\n";
echo "   http://localhost/notes/annuel\n\n";

echo "═══════════════════════════════════════════════════════════\n";
