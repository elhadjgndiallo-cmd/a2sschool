<?php
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
    "public/storage/photos" => "Photos d'administration",
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
?>