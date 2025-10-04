<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Events\ImageUploaded;

class ImageService
{
    /**
     * Redimensionne et enregistre une image téléchargée
     *
     * @param UploadedFile $image
     * @param string $path
     * @param int $width
     * @param int $height
     * @return string Le chemin de l'image enregistrée
     */
    public function resizeAndSaveImage(UploadedFile $image, string $path = 'profile_images', int $width = 300, int $height = 300): string
    {
        // S'assurer que le répertoire existe
        $this->ensureDirectoryExists($path);
        
        // Générer un nom de fichier unique
        $filename = uniqid('img_') . '.' . $image->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;
        
        // Créer une image à partir du fichier téléchargé
        $sourceImage = $this->createImageFromFile($image);
        
        if (!$sourceImage) {
            // Si l'image ne peut pas être créée, enregistrer l'original
            return $image->store($path, 'public');
        }
        
        // Obtenir les dimensions de l'image source
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // Créer une nouvelle image avec les dimensions souhaitées
        $targetImage = imagecreatetruecolor($width, $height);
        
        // Préserver la transparence pour les PNG
        if ($image->getClientOriginalExtension() == 'png') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $width, $height, $transparent);
        }
        
        // Redimensionner l'image
        imagecopyresampled(
            $targetImage, $sourceImage,
            0, 0, 0, 0,
            $width, $height, $sourceWidth, $sourceHeight
        );
        
        // Enregistrer l'image redimensionnée
        $storagePath = Storage::disk('public')->path($fullPath);
        $this->saveImage($targetImage, $storagePath, $image->getClientOriginalExtension());
        
        // Libérer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        
        // Déclencher l'événement de synchronisation
        event(new ImageUploaded($fullPath));
        
        return $fullPath;
    }
    
    /**
     * Crée une ressource d'image à partir d'un fichier téléchargé
     *
     * @param UploadedFile $file
     * @return resource|false
     */
    private function createImageFromFile(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getPathname();
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'png':
                return imagecreatefrompng($path);
            case 'gif':
                return imagecreatefromgif($path);
            default:
                return false;
        }
    }
    
    /**
     * Enregistre une ressource d'image dans un fichier
     *
     * @param resource $image
     * @param string $path
     * @param string $extension
     * @return bool
     */
    private function saveImage($image, string $path, string $extension): bool
    {
        $extension = strtolower($extension);
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $path, 85); // 85% de qualité
            case 'png':
                return imagepng($image, $path, 8); // Compression 8 (0-9)
            case 'gif':
                return imagegif($image, $path);
            default:
                return false;
        }
    }
    
    /**
     * S'assure que le répertoire existe
     *
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        $fullPath = Storage::disk('public')->path($path);
        
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    /**
     * Supprime une image du stockage
     *
     * @param string|null $path
     * @return bool
     */
    public function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        
        return Storage::disk('public')->delete($path);
    }
    
    /**
     * Synchronise une image vers public/storage pour XAMPP
     *
     * @param string $path
     * @return bool
     */
    private function syncImageToPublic(string $path): bool
    {
        $sourceFile = Storage::disk('public')->path($path);
        $targetFile = public_path('storage/' . $path);
        
        // Créer le dossier cible s'il n'existe pas
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Copier le fichier
        if (file_exists($sourceFile)) {
            return copy($sourceFile, $targetFile);
        }
        
        return false;
    }
}