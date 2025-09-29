<?php
/**
 * Script de synchronisation des images
 * Copie les images de storage/app/public vers public/storage
 */

echo "🔄 Synchronisation des images...\n";

$sourceDir = 'storage/app/public';
$targetDir = 'public/storage';

// Vérifier si le dossier source existe
if (!is_dir($sourceDir)) {
    echo "❌ Dossier source non trouvé: {$sourceDir}\n";
    exit(1);
}

// Créer le dossier cible s'il n'existe pas
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
    echo "📁 Dossier cible créé: {$targetDir}\n";
}

// Fonction récursive pour copier les fichiers
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
                echo "📄 Copié: {$file}\n";
            }
        }
    }
    closedir($dir);
}

// Copier tous les fichiers
copyDirectory($sourceDir, $targetDir);

echo "✅ Synchronisation terminée!\n";
echo "📊 Images disponibles dans public/storage/\n";

// Lister les fichiers copiés
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$imageCount = 0;
foreach ($files as $file) {
    if ($file->isFile() && preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $file->getFilename())) {
        $imageCount++;
    }
}

echo "🖼️  Nombre d'images synchronisées: {$imageCount}\n";
?>
