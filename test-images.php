<?php
// Script de test pour vérifier l'accès aux images
echo "<h2>Test d'accès aux images</h2>";

// Vérifier si le lien symbolique existe
$storageLink = __DIR__ . '/public/storage';
if (is_link($storageLink)) {
    echo "✅ Lien symbolique storage existe<br>";
} else {
    echo "❌ Lien symbolique storage manquant<br>";
}

// Vérifier les permissions
$storagePath = __DIR__ . '/storage/app/public';
if (is_dir($storagePath)) {
    echo "✅ Dossier storage/app/public existe<br>";
    if (is_writable($storagePath)) {
        echo "✅ Dossier storage/app/public est accessible en écriture<br>";
    } else {
        echo "❌ Dossier storage/app/public n'est pas accessible en écriture<br>";
    }
} else {
    echo "❌ Dossier storage/app/public n'existe pas<br>";
}

// Tester l'accès à une image
$testImage = __DIR__ . '/public/storage/profile_images';
if (is_dir($testImage)) {
    echo "✅ Dossier public/storage/profile_images existe<br>";
    $images = scandir($testImage);
    $imageFiles = array_filter($images, function($file) {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg']);
    });
    
    if (!empty($imageFiles)) {
        $firstImage = reset($imageFiles);
        echo "✅ Images trouvées dans le dossier (ex: $firstImage)<br>";
        
        // Tester l'URL de l'image
        $imageUrl = 'http://localhost/a2sschool/public/storage/profile_images/' . $firstImage;
        echo "🔗 URL de test: <a href='$imageUrl' target='_blank'>$imageUrl</a><br>";
    } else {
        echo "❌ Aucune image trouvée dans le dossier<br>";
    }
} else {
    echo "❌ Dossier public/storage/profile_images n'existe pas<br>";
}

// Afficher la configuration actuelle
echo "<h3>Configuration actuelle:</h3>";
echo "APP_URL: " . (getenv('APP_URL') ?: 'Non défini') . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>
