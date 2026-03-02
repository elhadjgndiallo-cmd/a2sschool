<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouleurParametre extends Model
{
    use HasFactory;

    protected $fillable = [
        'cle',
        'valeur',
        'description',
        'categorie',
    ];

    /**
     * Obtenir la valeur d'un paramètre de couleur
     */
    public static function getCouleur($cle, $defaultValue = null)
    {
        $parametre = self::where('cle', $cle)->first();
        return $parametre ? $parametre->valeur : $defaultValue;
    }

    /**
     * Définir la valeur d'un paramètre de couleur
     */
    public static function setCouleur($cle, $valeur, $description = null, $categorie = 'general')
    {
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
        return self::where('categorie', $categorie)->pluck('valeur', 'cle')->toArray();
    }

    /**
     * Initialiser les couleurs par défaut
     */
    public static function initialiserCouleursDefaut()
    {
        $couleursDefaut = [
            // Couleurs générales
            'header_bg' => '#34495e',
            'header_text' => '#ffffff',
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'success_color' => '#28a745',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#17a2b8',
            
            // Couleurs des bulletins
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
            
            // Couleurs des résultats
            'resultat_header_bg' => '#34495e',
            'resultat_header_text' => '#ffffff',
            'resultat_table_header_bg' => '#34495e',
            'resultat_table_header_text' => '#ffffff',
            'resultat_table_border' => '#2c3e50',
            'resultat_moyenne_bg' => '#f8f9fa',
            'resultat_moyenne_text' => '#28a745',
            'resultat_rang_bg' => '#f8f9fa',
            'resultat_rang_text' => '#007bff',
            
            // Couleurs des documents
            'document_header_bg' => '#34495e',
            'document_header_text' => '#ffffff',
            'document_title_bg' => '#6c757d',
            'document_title_text' => '#ffffff',
            'document_border' => '#2c3e50',
            'document_footer_bg' => '#f8f9fa',
            'document_footer_text' => '#6c757d',
        ];

        foreach ($couleursDefaut as $cle => $valeur) {
            $categorie = 'general';
            if (strpos($cle, 'bulletin_') === 0) {
                $categorie = 'bulletin';
            } elseif (strpos($cle, 'resultat_') === 0) {
                $categorie = 'resultat';
            } elseif (strpos($cle, 'document_') === 0) {
                $categorie = 'document';
            }
            
            self::setCouleur($cle, $valeur, ucfirst(str_replace('_', ' ', $cle)), $categorie);
        }
    }
}
