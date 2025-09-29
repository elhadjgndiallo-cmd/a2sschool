<?php
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
?>