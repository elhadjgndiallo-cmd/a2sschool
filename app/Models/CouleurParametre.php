<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CouleurParametre extends Model
{
    use HasFactory;

    protected $fillable = [
        'cle',
        'valeur',
        'description',
        'categorie',
    ];

    public static function tableDisponible(): bool
    {
        try {
            return Schema::hasTable((new static)->getTable());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Couleurs par défaut (utilisées si la table n'existe pas encore).
     */
    public static function couleursDefaut(): array
    {
        return [
            'header_bg' => '#34495e',
            'header_text' => '#ffffff',
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'success_color' => '#28a745',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#17a2b8',
            'bulletin_header_bg' => '#34495e',
            'bulletin_header_text' => '#ffffff',
            'bulletin_table_header_bg' => '#34495e',
            'bulletin_table_header_text' => '#ffffff',
            'bulletin_table_border' => '#2c3e50',
            'bulletin_success_bg' => '#f8f9fa',
            'bulletin_success_text' => '#28a745',
            'bulletin_danger_bg' => '#f8f9fa',
            'bulletin_danger_text' => '#dc3545',
            'bulletin_footer_bg' => '#f8f9fa',
            'bulletin_footer_text' => '#2c3e50',
            'resultat_header_bg' => '#34495e',
            'resultat_header_text' => '#ffffff',
            'resultat_table_header_bg' => '#34495e',
            'resultat_table_header_text' => '#ffffff',
            'resultat_table_border' => '#2c3e50',
            'resultat_moyenne_bg' => '#f8f9fa',
            'resultat_moyenne_text' => '#28a745',
            'resultat_rang_bg' => '#f8f9fa',
            'resultat_rang_text' => '#007bff',
            'document_header_bg' => '#34495e',
            'document_header_text' => '#ffffff',
            'document_title_bg' => '#6c757d',
            'document_title_text' => '#ffffff',
            'document_border' => '#2c3e50',
            'document_footer_bg' => '#f8f9fa',
            'document_footer_text' => '#6c757d',
        ];
    }

    /**
     * Ensemble des couleurs partagé avec les vues Blade.
     */
    public static function getCouleursPourVues(): array
    {
        return [
            'general' => self::getCouleursParCategorie('general'),
            'bulletin' => self::getCouleursParCategorie('bulletin'),
            'resultat' => self::getCouleursParCategorie('resultat'),
            'document' => self::getCouleursParCategorie('document'),
        ];
    }

    /**
     * Obtenir la valeur d'un paramètre de couleur
     */
    public static function getCouleur($cle, $defaultValue = null)
    {
        if (!self::tableDisponible()) {
            return self::couleursDefaut()[$cle] ?? $defaultValue;
        }

        $parametre = self::where('cle', $cle)->first();

        return $parametre ? $parametre->valeur : ($defaultValue ?? (self::couleursDefaut()[$cle] ?? null));
    }

    /**
     * Définir la valeur d'un paramètre de couleur
     */
    public static function setCouleur($cle, $valeur, $description = null, $categorie = null)
    {
        if (!self::tableDisponible()) {
            return null;
        }

        // Déterminer automatiquement la catégorie à partir de la clé
        if ($categorie === null) {
            $categorie = self::detecterCategorieParCle($cle);
        }

        return self::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => $valeur,
                'description' => $description,
                'categorie' => $categorie,
            ]
        );
    }

    /**
     * Obtenir toutes les couleurs par catégorie
     */
    public static function getCouleursParCategorie($categorie)
    {
        if (!self::tableDisponible()) {
            return self::filtrerCouleursParCategorie(self::couleursDefaut(), $categorie);
        }

        // Tolérance: récupérer la catégorie + les clés cohérentes même si
        // certaines lignes ont été enregistrées avec une mauvaise catégorie.
        $query = self::query()->where('categorie', $categorie);

        if ($categorie === 'bulletin') {
            $query->orWhere('cle', 'like', 'bulletin\_%');
        } elseif ($categorie === 'resultat') {
            $query->orWhere('cle', 'like', 'resultat\_%');
        } elseif ($categorie === 'document') {
            $query->orWhere('cle', 'like', 'document\_%');
        } elseif ($categorie === 'general') {
            $query->orWhere(function ($q) {
                $q->where('cle', 'not like', 'bulletin\_%')
                  ->where('cle', 'not like', 'resultat\_%')
                  ->where('cle', 'not like', 'document\_%');
            });
        }

        return $query->pluck('valeur', 'cle')->toArray();
    }

    private static function filtrerCouleursParCategorie(array $couleurs, string $categorie): array
    {
        return array_filter(
            $couleurs,
            fn (string $cle) => self::detecterCategorieParCle($cle) === $categorie,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Détecter la catégorie à partir du nom de la clé
     */
    private static function detecterCategorieParCle($cle)
    {
        if (strpos($cle, 'bulletin_') === 0) {
            return 'bulletin';
        }
        if (strpos($cle, 'resultat_') === 0) {
            return 'resultat';
        }
        if (strpos($cle, 'document_') === 0) {
            return 'document';
        }
        return 'general';
    }

    /**
     * Initialiser les couleurs par défaut
     */
    public static function initialiserCouleursDefaut()
    {
        if (!self::tableDisponible()) {
            return;
        }

        foreach (self::couleursDefaut() as $cle => $valeur) {
            self::setCouleur($cle, $valeur, ucfirst(str_replace('_', ' ', $cle)));
        }
    }
}
