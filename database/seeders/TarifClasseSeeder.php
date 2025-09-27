<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TarifClasse;
use App\Models\Classe;

class TarifClasseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a des classes
        $classes = Classe::all();
        if ($classes->isEmpty()) {
            $this->command->error('Aucune classe trouvée. Veuillez d\'abord exécuter ClasseSeeder.');
            return;
        }

        $this->command->info('Création des tarifs par classe...');

        $anneeScolaire = '2024-2025';

        // Tarifs par niveau
        $tarifsParNiveau = [
            'CP' => [
                'frais_inscription' => 50000,
                'frais_scolarite_mensuel' => 150000,
                'frais_cantine_mensuel' => 50000,
                'frais_transport_mensuel' => 30000,
                'frais_uniforme' => 25000,
                'frais_livres' => 40000,
                'frais_autres' => 10000
            ],
            'CE1' => [
                'frais_inscription' => 50000,
                'frais_scolarite_mensuel' => 150000,
                'frais_cantine_mensuel' => 50000,
                'frais_transport_mensuel' => 30000,
                'frais_uniforme' => 25000,
                'frais_livres' => 40000,
                'frais_autres' => 10000
            ],
            'CE2' => [
                'frais_inscription' => 50000,
                'frais_scolarite_mensuel' => 150000,
                'frais_cantine_mensuel' => 50000,
                'frais_transport_mensuel' => 30000,
                'frais_uniforme' => 25000,
                'frais_livres' => 40000,
                'frais_autres' => 10000
            ],
            'CM1' => [
                'frais_inscription' => 50000,
                'frais_scolarite_mensuel' => 150000,
                'frais_cantine_mensuel' => 50000,
                'frais_transport_mensuel' => 30000,
                'frais_uniforme' => 25000,
                'frais_livres' => 40000,
                'frais_autres' => 10000
            ],
            'CM2' => [
                'frais_inscription' => 50000,
                'frais_scolarite_mensuel' => 150000,
                'frais_cantine_mensuel' => 50000,
                'frais_transport_mensuel' => 30000,
                'frais_uniforme' => 25000,
                'frais_livres' => 40000,
                'frais_autres' => 10000
            ],
            '6ème' => [
                'frais_inscription' => 75000,
                'frais_scolarite_mensuel' => 200000,
                'frais_cantine_mensuel' => 60000,
                'frais_transport_mensuel' => 40000,
                'frais_uniforme' => 30000,
                'frais_livres' => 60000,
                'frais_autres' => 15000
            ],
            '5ème' => [
                'frais_inscription' => 75000,
                'frais_scolarite_mensuel' => 200000,
                'frais_cantine_mensuel' => 60000,
                'frais_transport_mensuel' => 40000,
                'frais_uniforme' => 30000,
                'frais_livres' => 60000,
                'frais_autres' => 15000
            ],
            '4ème' => [
                'frais_inscription' => 75000,
                'frais_scolarite_mensuel' => 200000,
                'frais_cantine_mensuel' => 60000,
                'frais_transport_mensuel' => 40000,
                'frais_uniforme' => 30000,
                'frais_livres' => 60000,
                'frais_autres' => 15000
            ],
            '3ème' => [
                'frais_inscription' => 75000,
                'frais_scolarite_mensuel' => 200000,
                'frais_cantine_mensuel' => 60000,
                'frais_transport_mensuel' => 40000,
                'frais_uniforme' => 30000,
                'frais_livres' => 60000,
                'frais_autres' => 15000
            ],
            '2nde' => [
                'frais_inscription' => 100000,
                'frais_scolarite_mensuel' => 250000,
                'frais_cantine_mensuel' => 70000,
                'frais_transport_mensuel' => 50000,
                'frais_uniforme' => 35000,
                'frais_livres' => 80000,
                'frais_autres' => 20000
            ],
            '1ère' => [
                'frais_inscription' => 100000,
                'frais_scolarite_mensuel' => 250000,
                'frais_cantine_mensuel' => 70000,
                'frais_transport_mensuel' => 50000,
                'frais_uniforme' => 35000,
                'frais_livres' => 80000,
                'frais_autres' => 20000
            ],
            'Tle' => [
                'frais_inscription' => 100000,
                'frais_scolarite_mensuel' => 250000,
                'frais_cantine_mensuel' => 70000,
                'frais_transport_mensuel' => 50000,
                'frais_uniforme' => 35000,
                'frais_livres' => 80000,
                'frais_autres' => 20000
            ]
        ];

        foreach ($classes as $classe) {
            // Déterminer le niveau de la classe
            $niveau = $this->determinerNiveau($classe->nom);
            
            if (isset($tarifsParNiveau[$niveau])) {
                $tarifData = $tarifsParNiveau[$niveau];
                
                // Vérifier qu'il n'y a pas déjà un tarif pour cette classe et année
                $existingTarif = TarifClasse::where('classe_id', $classe->id)
                    ->where('annee_scolaire', $anneeScolaire)
                    ->first();

                if (!$existingTarif) {
                    TarifClasse::create([
                        'classe_id' => $classe->id,
                        'annee_scolaire' => $anneeScolaire,
                        'frais_inscription' => $tarifData['frais_inscription'],
                        'frais_scolarite_mensuel' => $tarifData['frais_scolarite_mensuel'],
                        'frais_cantine_mensuel' => $tarifData['frais_cantine_mensuel'],
                        'frais_transport_mensuel' => $tarifData['frais_transport_mensuel'],
                        'frais_uniforme' => $tarifData['frais_uniforme'],
                        'frais_livres' => $tarifData['frais_livres'],
                        'frais_autres' => $tarifData['frais_autres'],
                        'paiement_par_tranches' => true,
                        'nombre_tranches' => 12,
                        'periode_tranche' => 'mensuel',
                        'actif' => true,
                        'description' => "Tarifs pour la classe {$classe->nom} - Année scolaire {$anneeScolaire}"
                    ]);

                    $this->command->info("Tarif créé pour la classe {$classe->nom} - {$niveau}");
                } else {
                    $this->command->info("Tarif déjà existant pour la classe {$classe->nom}");
                }
            } else {
                $this->command->warn("Aucun tarif défini pour le niveau: {$niveau} (classe: {$classe->nom})");
            }
        }

        $this->command->info('Tarifs par classe créés avec succès !');
    }

    private function determinerNiveau($nomClasse)
    {
        $nomClasse = strtoupper($nomClasse);
        
        if (strpos($nomClasse, 'CP') !== false) return 'CP';
        if (strpos($nomClasse, 'CE1') !== false) return 'CE1';
        if (strpos($nomClasse, 'CE2') !== false) return 'CE2';
        if (strpos($nomClasse, 'CM1') !== false) return 'CM1';
        if (strpos($nomClasse, 'CM2') !== false) return 'CM2';
        if (strpos($nomClasse, '6') !== false) return '6ème';
        if (strpos($nomClasse, '5') !== false) return '5ème';
        if (strpos($nomClasse, '4') !== false) return '4ème';
        if (strpos($nomClasse, '3') !== false) return '3ème';
        if (strpos($nomClasse, '2NDE') !== false || strpos($nomClasse, '2ND') !== false) return '2nde';
        if (strpos($nomClasse, '1ERE') !== false || strpos($nomClasse, '1ER') !== false) return '1ère';
        if (strpos($nomClasse, 'TLE') !== false || strpos($nomClasse, 'TERMINALE') !== false) return 'Tle';
        
        return 'CP'; // Par défaut
    }
}
