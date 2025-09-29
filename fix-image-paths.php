<?php
/**
 * Script pour corriger automatiquement tous les chemins d'images
 */

echo "🔧 Correction des chemins d'images...\n";

$files = [
    'resources/views/cartes-scolaires/show.blade.php',
    'resources/views/cartes-scolaires/imprimer.blade.php',
    'resources/views/teacher/historique-notes.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/eleves/reinscription.blade.php',
    'resources/views/etablissement/edit-informations.blade.php',
    'resources/views/student/emploi-temps.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/utilisateurs/index.blade.php',
    'resources/views/cartes-enseignants/imprimer.blade.php',
    'resources/views/eleves/edit.blade.php',
    'resources/views/enseignants/edit_old.blade.php',
    'resources/views/enseignants/index.blade.php',
    'resources/views/personnel-administration/index.blade.php',
    'resources/views/cartes-enseignants/index.blade.php',
    'resources/views/admin/accounts/index.blade.php',
    'resources/views/teacher/historique-absences.blade.php',
    'resources/views/admin/accounts/edit.blade.php',
    'resources/views/admin/accounts/show.blade.php',
    'resources/views/personnel-administration/edit.blade.php',
    'resources/views/personnel-administration/show.blade.php',
    'resources/views/personnel-administration/permissions.blade.php',
    'resources/views/cartes-enseignants/show.blade.php',
    'resources/views/enseignants/show.blade.php',
    'resources/views/admin/accounts/permissions.blade.php'
];

$fixedCount = 0;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Remplacer les patterns courants
        $patterns = [
            // Pattern 1: asset('storage/' . $variable)
            '/asset\(\'storage\/\' \. \$[^)]+\)/' => function($matches) {
                return 'asset(\'images/profile_images/\' . basename($' . substr($matches[0], 20, -2) . '))';
            },
            
            // Pattern 2: asset('storage/profile_images/...')
            '/asset\(\'storage\/profile_images\/([^\']+)\'\)/' => 'asset(\'images/profile_images/$1\')',
            
            // Pattern 3: asset('storage/etablissement/...')
            '/asset\(\'storage\/etablissement\/([^\']+)\'\)/' => 'asset(\'images/etablissement/$1\')',
        ];
        
        // Appliquer les remplacements
        foreach ($patterns as $pattern => $replacement) {
            if (is_callable($replacement)) {
                $content = preg_replace_callback($pattern, $replacement, $content);
            } else {
                $content = preg_replace($pattern, $replacement, $content);
            }
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "✅ Corrigé: {$file}\n";
            $fixedCount++;
        } else {
            echo "⏭️  Aucun changement: {$file}\n";
        }
    } else {
        echo "❌ Fichier non trouvé: {$file}\n";
    }
}

echo "\n🎯 Résumé:\n";
echo "📁 Fichiers traités: " . count($files) . "\n";
echo "✅ Fichiers corrigés: {$fixedCount}\n";
echo "⏭️  Fichiers inchangés: " . (count($files) - $fixedCount) . "\n";

echo "\n🔄 Synchronisation des images d'établissement...\n";
if (is_dir('storage/app/public/etablissement')) {
    if (!is_dir('public/images/etablissement')) {
        mkdir('public/images/etablissement', 0755, true);
    }
    
    // Copier les images d'établissement
    $etablissementFiles = glob('storage/app/public/etablissement/*');
    foreach ($etablissementFiles as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            $dest = 'public/images/etablissement/' . $filename;
            copy($file, $dest);
            echo "📄 Copié: {$filename}\n";
        }
    }
}

echo "\n✅ Correction terminée!\n";
?>

