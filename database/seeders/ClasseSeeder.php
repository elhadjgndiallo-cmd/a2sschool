<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Matiere;

class ClasseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Classes
        $classes = [
            ['nom' => '6ème A', 'niveau' => '6ème', 'section' => 'A', 'effectif_max' => 30],
            ['nom' => '6ème B', 'niveau' => '6ème', 'section' => 'B', 'effectif_max' => 30],
            ['nom' => '5ème A', 'niveau' => '5ème', 'section' => 'A', 'effectif_max' => 28],
            ['nom' => '4ème A', 'niveau' => '4ème', 'section' => 'A', 'effectif_max' => 25],
            ['nom' => '3ème A', 'niveau' => '3ème', 'section' => 'A', 'effectif_max' => 25],
        ];

        foreach ($classes as $classeData) {
            Classe::create($classeData);
        }

        // Matières
        $matieres = [
            ['nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 4, 'couleur' => '#3498db'],
            ['nom' => 'Français', 'code' => 'FR', 'coefficient' => 4, 'couleur' => '#e74c3c'],
            ['nom' => 'Histoire-Géographie', 'code' => 'HIST-GEO', 'coefficient' => 3, 'couleur' => '#f39c12'],
            ['nom' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'coefficient' => 2, 'couleur' => '#27ae60'],
            ['nom' => 'Physique-Chimie', 'code' => 'PC', 'coefficient' => 2, 'couleur' => '#9b59b6'],
            ['nom' => 'Anglais', 'code' => 'ANG', 'coefficient' => 3, 'couleur' => '#1abc9c'],
            ['nom' => 'Éducation Physique et Sportive', 'code' => 'EPS', 'coefficient' => 1, 'couleur' => '#e67e22'],
            ['nom' => 'Arts Plastiques', 'code' => 'ARTS', 'coefficient' => 1, 'couleur' => '#f1c40f'],
        ];

        foreach ($matieres as $matiereData) {
            Matiere::create($matiereData);
        }
    }
}
