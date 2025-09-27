<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmploiTemps;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;

class EmploiTempsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les données nécessaires
        $classes = Classe::all();
        $matieres = Matiere::all();
        $enseignants = Enseignant::all();

        if ($classes->isEmpty() || $matieres->isEmpty() || $enseignants->isEmpty()) {
            $this->command->info('Veuillez d\'abord créer des classes, matières et enseignants');
            return;
        }

        // Créer des emplois du temps pour chaque classe
        foreach ($classes as $classe) {
            // Assigner quelques matières à chaque classe
            $matieresClasse = $matieres->random(min(5, $matieres->count()));
            
            foreach ($matieresClasse as $matiere) {
                // Assigner un enseignant aléatoire à chaque matière
                $enseignant = $enseignants->random();
                
                // Créer l'emploi du temps
                EmploiTemps::create([
                    'classe_id' => $classe->id,
                    'matiere_id' => $matiere->id,
                    'enseignant_id' => $enseignant->id,
                    'jour_semaine' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'][rand(0, 4)],
                    'heure_debut' => sprintf('%02d:00', rand(8, 15)),
                    'heure_fin' => sprintf('%02d:00', rand(9, 16)),
                    'salle' => 'Salle ' . rand(1, 20),
                    'type_cours' => ['cours', 'tp', 'td'][rand(0, 2)],
                    'actif' => true,
                ]);

                // Associer l'enseignant à la matière s'il ne l'est pas déjà
                if (!$enseignant->matieres()->where('matiere_id', $matiere->id)->exists()) {
                    $enseignant->matieres()->attach($matiere->id);
                }
            }
        }

        $this->command->info('Emplois du temps créés avec succès');
    }
}
