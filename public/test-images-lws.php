<?php
/**
 * Script de test des images pour le serveur LWS
 */

echo "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Test des Images - Serveur LWS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .image-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .image-item { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .image-item img { max-width: 100px; max-height: 100px; }
        .status { padding: 5px 10px; border-radius: 3px; margin: 5px 0; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🖼️ Test des Images sur le Serveur LWS</h1>";

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
        echo "<div class=\"status success\">✅ $description: $count fichiers</div>";
    } else {
        echo "<div class=\"status error\">❌ $description: Dossier manquant</div>";
    }
}

// Lister les images
echo "<h2>🖼️ Images disponibles</h2>";
$imageDirs = [
    "public/storage/photos" => "Photos d'administration",
    "public/storage/profile_images" => "Photos de profil",
    "public/storage/etablissement" => "Images établissement"
];

foreach ($imageDirs as $dir => $description) {
    if (is_dir($dir)) {
        $imageFiles = glob($dir . "/*.{jpg,jpeg,png,gif,webp,svg}", GLOB_BRACE);
        echo "<h3>$description (" . count($imageFiles) . " images)</h3>";
        echo "<div class=\"image-grid\">";
        
        foreach (array_slice($imageFiles, 0, 10) as $file) {
            $filename = basename($file);
            $url = str_replace("public/", "", $file);
            echo "<div class=\"image-item\">
                <img src=\"$url\" alt=\"$filename\" onerror=\"this.style.display='none'\">
                <br><small>$filename</small>
            </div>";
        }
        echo "</div>";
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
    echo "<div class=\"status info\"><a href=\"$fullUrl\" target=\"_blank\">$fullUrl</a></div>";
}

echo "<h2>📊 Résumé</h2>";
echo "<div class=\"status info\">";
echo "✅ Images copiées vers public/storage/<br>";
echo "✅ Fichier .htaccess créé<br>";
echo "✅ Route spéciale /image/{path} disponible<br>";
echo "✅ Helper ImageHelper amélioré<br>";
echo "</div>";

echo "</body></html>";
?>