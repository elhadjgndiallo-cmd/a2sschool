<?php

namespace App\Helpers;

class ImageSyncHelper
{
    /**
     * Synchroniser les images après un upload
     * Nécessaire pour XAMPP sur Windows qui ne gère pas les liens symboliques
     */
    public static function syncImages()
    {
        $sourceDir = storage_path('app/public');
        $targetDir = public_path('storage');
        
        if (!is_dir($sourceDir)) {
            return false;
        }
        
        // Créer le dossier cible s'il n'existe pas
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        return self::copyDirectory($sourceDir, $targetDir);
    }
    
    /**
     * Copier récursivement un dossier
     */
    private static function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                if (is_dir($srcFile)) {
                    self::copyDirectory($srcFile, $dstFile);
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
    
    /**
     * Synchroniser une image spécifique
     */
    public static function syncImage($imagePath)
    {
        $sourceFile = storage_path('app/public/' . $imagePath);
        $targetFile = public_path('storage/' . $imagePath);
        
        if (file_exists($sourceFile)) {
            // Créer le dossier parent s'il n'existe pas
            $targetDir = dirname($targetFile);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            return copy($sourceFile, $targetFile);
        }
        
        return false;
    }
}

