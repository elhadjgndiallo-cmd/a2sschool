<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eleve;
use App\Models\Utilisateur;
use App\Models\Classe;
use App\Models\ParentModel;

class EleveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs élèves
        $elevesUtilisateurs = Utilisateur::where('role', 'student')->get();
        $classes = Classe::all();
        $parents = ParentModel::all();

        foreach ($elevesUtilisateurs as $index => $utilisateur) {
            $eleve = Eleve::create([
                'utilisateur_id' => $utilisateur->id,
                'classe_id' => $classes->random()->id,
                'numero_etudiant' => 'ETU' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'date_inscription' => now()->subMonths(rand(1, 12)),
                'statut' => 'inscrit',
                'niveau_precedent' => $index > 0 ? 'CP' : null,
                'etablissement_precedent' => $index > 0 ? 'École Primaire Centrale' : null,
                'observations' => 'Élève motivé et assidu',
            ]);

            // Associer des parents (si disponibles)
            if ($parents->count() > 0) {
                $parentIds = $parents->random(min(2, $parents->count()))->pluck('id');
                foreach ($parentIds as $parentId) {
                    $eleve->parents()->attach($parentId, [
                        'responsable_legal' => true,
                        'autorise_sortie' => true,
                        'contact_urgence' => true,
                    ]);
                }
            }
        }

        // Mettre à jour l'effectif des classes
        foreach ($classes as $classe) {
            $effectif = $classe->eleves()->count();
            $classe->update(['effectif_actuel' => $effectif]);
        }
    }
}
