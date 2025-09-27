<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Matiere;

class MatiereSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Supprimer toutes les matières existantes
        Matiere::truncate();

        $matieres = [
            ['nom' => 'Anglais', 'code' => 'ANG', 'coefficient' => 3, 'couleur' => '#FF6B6B', 'description' => 'Langue anglaise et littérature', 'actif' => true],
            ['nom' => 'Français', 'code' => 'FR', 'coefficient' => 4, 'couleur' => '#4ECDC4', 'description' => 'Langue française et littérature', 'actif' => true],
            ['nom' => 'Physique', 'code' => 'PHY', 'coefficient' => 3, 'couleur' => '#45B7D1', 'description' => 'Sciences physiques', 'actif' => true],
            ['nom' => 'Chimie', 'code' => 'CHI', 'coefficient' => 3, 'couleur' => '#96CEB4', 'description' => 'Sciences chimiques', 'actif' => true],
            ['nom' => 'Mathématique', 'code' => 'MATH', 'coefficient' => 4, 'couleur' => '#FFEAA7', 'description' => 'Mathématiques', 'actif' => true],
            ['nom' => 'Philosophie', 'code' => 'PHILO', 'coefficient' => 2, 'couleur' => '#DDA0DD', 'description' => 'Philosophie et éthique', 'actif' => true],
            ['nom' => 'Biologie', 'code' => 'BIO', 'coefficient' => 3, 'couleur' => '#98D8C8', 'description' => 'Sciences biologiques', 'actif' => true],
            ['nom' => 'Géologie', 'code' => 'GEO', 'coefficient' => 2, 'couleur' => '#F7DC6F', 'description' => 'Sciences de la terre', 'actif' => true],
            ['nom' => 'ECM', 'code' => 'ECM', 'coefficient' => 2, 'couleur' => '#BB8FCE', 'description' => 'Éducation Civique et Morale', 'actif' => true],
            ['nom' => 'Économie', 'code' => 'ECO', 'coefficient' => 3, 'couleur' => '#F8C471', 'description' => 'Sciences économiques', 'actif' => true]
        ];

        foreach ($matieres as $matiere) {
            Matiere::create($matiere);
        }
    }
}
