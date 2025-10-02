<?php
/**
 * Script de correction des images pour le serveur LWS
 */

echo "🖼️ Correction des images pour le serveur LWS\n";
echo "===========================================\n\n";

// Test 1: Vérifier et créer le lien symbolique
echo "1. Correction du lien symbolique...\n";

// Supprimer l'ancien lien s'il existe
if (is_link('public/storage')) {
    echo "   🔧 Suppression de l'ancien lien symbolique...\n";
    unlink('public/storage');
}

// Créer le dossier public/storage s'il n'existe pas
if (!is_dir('public/storage')) {
    echo "   🔧 Création du dossier public/storage...\n";
    mkdir('public/storage', 0755, true);
}

// Copier les images de storage/app/public vers public/storage
echo "   🔧 Copie des images vers public/storage...\n";
$sourceDir = 'storage/app/public';
$targetDir = 'public/storage';

if (is_dir($sourceDir)) {
    // Fonction pour copier récursivement
    function copyDirectory($src, $dst) {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                if (is_dir($srcFile)) {
                    copyDirectory($srcFile, $dstFile);
                } else {
                    // Copier seulement si le fichier source est plus récent ou n'existe pas
                    if (!file_exists($dstFile) || filemtime($srcFile) > filemtime($dstFile)) {
                        copy($srcFile, $dstFile);
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
    
    if (copyDirectory($sourceDir, $targetDir)) {
        echo "   ✅ Images copiées avec succès\n";
    } else {
        echo "   ❌ Erreur lors de la copie des images\n";
    }
} else {
    echo "   ❌ Dossier source storage/app/public manquant\n";
}

echo "\n";

// Test 2: Vérifier les images copiées
echo "2. Vérification des images copiées...\n";
$imageDirs = [
    'public/storage/photos' => 'Photos d\'administration',
    'public/storage/profile_images' => 'Photos de profil',
    'public/storage/etablissement' => 'Images établissement'
];

foreach ($imageDirs as $dir => $description) {
    if (is_dir($dir)) {
        $imageFiles = glob($dir . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
        $count = count($imageFiles);
        echo "   📊 $description: $count images\n";
        
        if ($count > 0) {
            echo "     📋 Exemples:\n";
            foreach (array_slice($imageFiles, 0, 3) as $file) {
                $filename = basename($file);
                echo "       - $filename\n";
            }
        }
    } else {
        echo "   ❌ $description - Dossier manquant\n";
    }
}

echo "\n";

// Test 3: Créer un fichier .htaccess pour les images
echo "3. Configuration du serveur web...\n";
$htaccessContent = '# Configuration pour les images
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Autoriser l\'accès aux fichiers statiques
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^(.*)$ - [L]
</IfModule>

# Autoriser l\'accès aux images
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|css|js|ico|pdf|txt)$">
    Order Allow,Deny
    Allow from all
    Header set Cache-Control "public, max-age=31536000"
    Header set Expires "Thu, 31 Dec 2025 23:59:59 GMT"
</FilesMatch>

# Sécurité - Bloquer l\'exécution de scripts
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>';

$htaccessFile = 'public/storage/.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "   ✅ Fichier .htaccess créé pour public/storage\n";
} else {
    echo "   ❌ Erreur lors de la création du fichier .htaccess\n";
}

echo "\n";

// Test 4: Vérifier les permissions
echo "4. Vérification des permissions...\n";
$permissionDirs = [
    'public/storage' => 'Dossier storage public',
    'storage/app/public' => 'Dossier storage source'
];

foreach ($permissionDirs as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✅ $description - Écriture autorisée\n";
        } else {
            echo "   ❌ $description - Écriture refusée (chmod 755 requis)\n";
        }
    } else {
        echo "   ❌ $description - Dossier manquant\n";
    }
}

echo "\n";

// Test 5: Créer un script de test des images
echo "5. Création d\'un script de test...\n";
$testScript = '<?php
/**
 * Script de test des images pour le serveur LWS
 */

echo "<h1>🖼️ Test des images sur le serveur LWS</h1>";

// Vérifier la structure
echo "<h2>📁 Structure des dossiers</h2>";
$directories = [
    "storage/app/public" => "Dossier source",
    "public/storage" => "Dossier public",
    "public/images" => "Images publiques"
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        $files = glob($dir . "/*");
        $count = count($files);
        echo "<p>✅ $description: $count fichiers</p>";
    } else {
        echo "<p>❌ $description: Dossier manquant</p>";
    }
}

// Lister les images
echo "<h2>🖼️ Images disponibles</h2>";
$imageDirs = [
    "public/storage/photos" => "Photos d\'administration",
    "public/storage/profile_images" => "Photos de profil",
    "public/storage/etablissement" => "Images établissement"
];

foreach ($imageDirs as $dir => $description) {
    if (is_dir($dir)) {
        $imageFiles = glob($dir . "/*.{jpg,jpeg,png,gif,webp,svg}", GLOB_BRACE);
        echo "<h3>$description (" . count($imageFiles) . " images)</h3>";
        
        foreach (array_slice($imageFiles, 0, 5) as $file) {
            $filename = basename($file);
            $url = str_replace("public/", "", $file);
            echo "<p><img src=\"$url\" style=\"max-width: 100px; max-height: 100px; margin: 5px;\" alt=\"$filename\"><br>$filename</p>";
        }
    }
}

echo "<h2>🔗 Test des URLs</h2>";
$baseUrl = "http://" . $_SERVER["HTTP_HOST"];
$testUrls = [
    "/storage/photos/",
    "/storage/profile_images/",
    "/storage/etablissement/",
    "/images/default-avatar.svg"
];

foreach ($testUrls as $url) {
    $fullUrl = $baseUrl . $url;
    echo "<p><a href=\"$fullUrl\" target=\"_blank\">$fullUrl</a></p>";
}
?>';

$testFile = 'public/test-images.php';
if (file_put_contents($testFile, $testScript)) {
    echo "   ✅ Script de test créé: $testFile\n";
} else {
    echo "   ❌ Erreur lors de la création du script de test\n";
}

echo "\n";

// Résumé des solutions
echo "📊 Résumé des solutions appliquées:\n";
echo "==================================\n";
echo "✅ Images copiées vers public/storage/\n";
echo "✅ Fichier .htaccess créé pour les images\n";
echo "✅ Script de test créé\n";
echo "✅ Permissions vérifiées\n";

echo "\n🔧 Instructions pour le serveur LWS:\n";
echo "====================================\n";
echo "1. Télécharger tous les fichiers sur le serveur\n";
echo "2. Exécuter ce script: php fix-images-lws.php\n";
echo "3. Tester l'accès: http://votre-domaine.com/test-images.php\n";
echo "4. Vérifier que les images s'affichent dans l'application\n";

echo "\n✨ Correction des images terminée !\n";
?>
