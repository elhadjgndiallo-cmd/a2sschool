<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Génère l'URL complète d'une image stockée
     *
     * @param string|null $path
     * @param string $default
     * @return string
     */
    public static function getImageUrl(?string $path, string $default = null): string
    {
        if (empty($path)) {
            return $default ?? asset('images/default-avatar.svg');
        }

        // Si c'est déjà une URL complète, la retourner
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Vérifier si le fichier existe dans le storage public
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Vérifier si le fichier existe dans le dossier public
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Retourner l'image par défaut
        return $default ?? asset('images/default-avatar.svg');
    }

    /**
     * Vérifie si une image existe
     *
     * @param string|null $path
     * @return bool
     */
    public static function imageExists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Vérifier si c'est une URL complète
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Vérifier dans le storage public
        if (Storage::disk('public')->exists($path)) {
            return true;
        }

        // Vérifier dans le dossier public
        return file_exists(public_path($path));
    }

    /**
     * Génère le HTML pour afficher une image avec fallback
     *
     * @param string|null $path
     * @param string $alt
     * @param array $attributes
     * @return string
     */
    public static function imageTag(?string $path, string $alt = 'Image', array $attributes = []): string
    {
        $url = self::getImageUrl($path);
        
        $defaultAttributes = [
            'alt' => $alt,
            'class' => 'img-thumbnail',
            'style' => 'object-fit: cover;'
        ];

        $attributes = array_merge($defaultAttributes, $attributes);
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= " {$key}=\"{$value}\"";
        }

        return "<img src=\"{$url}\" {$attrString}>";
    }

    /**
     * Génère le HTML pour afficher une image de profil avec fallback
     *
     * @param string|null $path
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public static function profileImage(?string $path, string $name = 'Utilisateur', array $attributes = []): string
    {
        $defaultAttributes = [
            'class' => 'img-thumbnail rounded-circle',
            'style' => 'width: 150px; height: 150px; object-fit: cover;'
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        if (self::imageExists($path)) {
            return self::imageTag($path, "Photo de {$name}", $attributes);
        }

        // Retourner un placeholder avec initiales
        $initials = self::getInitials($name);
        $bgColor = self::getColorFromName($name);
        
        $style = $attributes['style'] ?? '';
        $class = $attributes['class'] ?? '';
        
        return "<div class=\"{$class}\" style=\"{$style} background-color: {$bgColor}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 2rem;\">{$initials}</div>";
    }

    /**
     * Extrait les initiales d'un nom
     *
     * @param string $name
     * @return string
     */
    private static function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Génère une couleur basée sur le nom
     *
     * @param string $name
     * @return string
     */
    private static function getColorFromName(string $name): string
    {
        $colors = [
            '#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6',
            '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#8e44ad'
        ];
        
        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
    }
}
