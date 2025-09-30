<?php
// Test d'accès aux images sur le serveur
echo "<h1>Test d'accès aux images</h1>";

// Vérifier le lien symbolique
if (is_link("public/storage")) {
    echo "<p>✅ Lien symbolique public/storage existe</p>";
    echo "<p>Cible: " . readlink("public/storage") . "</p>";
} elseif (is_dir("public/storage")) {
    echo "<p>✅ Dossier public/storage existe</p>";
} else {
    echo "<p>❌ Lien symbolique manquant</p>";
}

// Lister les images disponibles
$imageDirs = [
    "public/storage/photos/admin" => "Photos admin",
    "public/storage/profile_images" => "Photos de profil",
    "public/storage/etablissement/logos" => "Logos"
];

foreach ($imageDirs as $dir => $label) {
    if (is_dir($dir)) {
        $files = glob($dir . "/*");
        $imageFiles = array_filter($files, function($file) {
            return is_file($file) && preg_match("/\.(jpg|jpeg|png|gif|webp)$/i", $file);
        });
        
        echo "<h3>$label (" . count($imageFiles) . " images)</h3>";
        
        if (count($imageFiles) > 0) {
            echo "<div style=\"display: flex; flex-wrap: wrap; gap: 10px;\">";
            foreach (array_slice($imageFiles, 0, 5) as $file) {
                $filename = basename($file);
                $url = str_replace("public/", "", $file);
                echo "<div style=\"border: 1px solid #ccc; padding: 10px; text-align: center;\">";
                echo "<img src=\"$url\" style=\"max-width: 100px; max-height: 100px; object-fit: cover;\"><br>";
                echo "<small>$filename</small><br>";
                echo "<a href=\"$url\" target=\"_blank\">Voir en grand</a>";
                echo "</div>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>❌ $label: Dossier inexistant</p>";
    }
}
?>