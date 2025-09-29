<?php
/**
 * Script de déploiement pour les images
 * À exécuter sur le serveur de production
 */

echo "🚀 Script de déploiement des images\n";
echo "=====================================\n\n";

// 1. Vérifier les permissions
echo "1️⃣ Vérification des permissions...\n";
$directories = [
    'storage/app/public',
    'public/storage',
    'public/images'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Dossier créé: {$dir}\n";
    } else {
        echo "✅ Dossier existe: {$dir}\n";
    }
    
    // Vérifier les permissions d'écriture
    if (is_writable($dir)) {
        echo "✅ Permissions OK: {$dir}\n";
    } else {
        echo "❌ Problème de permissions: {$dir}\n";
        chmod($dir, 0755);
        echo "🔧 Permissions corrigées: {$dir}\n";
    }
}

// 2. Créer le lien symbolique si possible
echo "\n2️⃣ Création du lien symbolique...\n";
$target = 'public/storage';
$link = 'storage/app/public';

if (is_link($target)) {
    echo "✅ Lien symbolique existe déjà\n";
} else {
    if (file_exists($target)) {
        echo "⚠️  Dossier {$target} existe déjà, suppression...\n";
        if (is_dir($target)) {
            system("rm -rf {$target}");
        }
    }
    
    if (symlink($link, $target)) {
        echo "✅ Lien symbolique créé: {$target} -> {$link}\n";
    } else {
        echo "❌ Impossible de créer le lien symbolique, copie des fichiers...\n";
        
        // Copier les fichiers si le lien symbolique échoue
        function copyDirectory($src, $dst) {
            if (!is_dir($dst)) {
                mkdir($dst, 0755, true);
            }
            
            $dir = opendir($src);
            while (($file = readdir($dir)) !== false) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($src . '/' . $file)) {
                        copyDirectory($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }
        
        copyDirectory($link, $target);
        echo "✅ Fichiers copiés dans {$target}\n";
    }
}

// 3. Vérifier les images existantes
echo "\n3️⃣ Vérification des images...\n";
$imagePaths = [
    'storage/app/public/profile_images/',
    'storage/app/public/etablissement/logos/',
    'storage/app/public/etablissement/cachets/',
    'storage/app/public/photos/',
    'public/images/'
];

$totalImages = 0;
foreach ($imagePaths as $path) {
    if (is_dir($path)) {
        $files = glob($path . '*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
        $count = count($files);
        $totalImages += $count;
        echo "📁 {$path}: {$count} images\n";
    }
}

echo "🖼️  Total d'images: {$totalImages}\n";

// 4. Tester l'accès aux images
echo "\n4️⃣ Test d'accès aux images...\n";
$testImages = [
    'public/storage/profile_images/img_68bddbb2175a5.png',
    'public/images/default-avatar.svg'
];

foreach ($testImages as $image) {
    if (file_exists($image)) {
        echo "✅ Accessible: {$image}\n";
    } else {
        echo "❌ Inaccessible: {$image}\n";
    }
}

// 5. Créer un script de synchronisation automatique
echo "\n5️⃣ Création du script de synchronisation...\n";
$syncScript = '<?php
// Script de synchronisation automatique des images
$source = "storage/app/public";
$target = "public/storage";

if (is_dir($source)) {
    if (is_link($target)) {
        echo "Lien symbolique OK\n";
    } else {
        // Copier les nouveaux fichiers
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($source . "/", "", $file->getPathname());
                $targetPath = $target . "/" . $relativePath;
                $targetDir = dirname($targetPath);
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                if (!file_exists($targetPath) || filemtime($file->getPathname()) > filemtime($targetPath)) {
                    copy($file->getPathname(), $targetPath);
                    echo "Synchronisé: {$relativePath}\n";
                }
            }
        }
    }
}
?>';

file_put_contents('sync-images-production.php', $syncScript);
echo "✅ Script de synchronisation créé: sync-images-production.php\n";

// 6. Instructions pour le serveur
echo "\n6️⃣ Instructions pour le serveur de production:\n";
echo "================================================\n";
echo "1. Exécutez: php artisan storage:link\n";
echo "2. Vérifiez les permissions: chmod -R 755 storage/\n";
echo "3. Vérifiez les permissions: chmod -R 755 public/storage/\n";
echo "4. Configurez votre serveur web pour servir les fichiers statiques\n";
echo "5. Testez l'accès: https://votre-domaine.com/storage/profile_images/img_68bddbb2175a5.png\n";
echo "6. Exécutez le script de sync si nécessaire: php sync-images-production.php\n";

echo "\n✅ Script de déploiement terminé!\n";
?>
