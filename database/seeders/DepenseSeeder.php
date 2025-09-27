<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Depense;
use App\Models\Enseignant;
use App\Models\Utilisateur;

class DepenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les enseignants
        $enseignants = Enseignant::all();
        
        if ($enseignants->count() == 0) {
            $this->command->warn('Aucun enseignant trouvé. Veuillez d\'abord exécuter les seeders de base.');
            return;
        }

        // Récupérer un utilisateur admin
        $admin = Utilisateur::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Aucun utilisateur admin trouvé.');
            return;
        }

        // Créer des dépenses de salaires pour les enseignants
        foreach ($enseignants as $enseignant) {
            // Salaire mensuel de l'enseignant
            $salaire = Depense::create([
                'libelle' => 'Salaire ' . $enseignant->nom . ' ' . $enseignant->prenom,
                'montant' => 800000, // 800,000 GNF par mois
                'date_depense' => now()->subMonth(),
                'type_depense' => 'salaire_enseignant',
                'description' => 'Salaire mensuel de l\'enseignant',
                'beneficiaire' => $enseignant->nom . ' ' . $enseignant->prenom,
                'statut' => 'paye',
                'mode_paiement' => 'virement',
                'reference_paiement' => 'VIR-SAL-' . $enseignant->id . '-' . now()->format('Ym'),
                'paye_par' => $admin->id,
                'date_paiement' => now()->subMonth(),
                'observations' => 'Salaire payé par virement bancaire'
            ]);

            // Salaire du mois en cours (en attente)
            Depense::create([
                'libelle' => 'Salaire ' . $enseignant->nom . ' ' . $enseignant->prenom,
                'montant' => 800000,
                'date_depense' => now()->toDateString(),
                'type_depense' => 'salaire_enseignant',
                'description' => 'Salaire mensuel de l\'enseignant',
                'beneficiaire' => $enseignant->nom . ' ' . $enseignant->prenom,
                'statut' => 'en_attente',
                'observations' => 'Salaire du mois en cours'
            ]);
        }

        // Créer d'autres types de dépenses
        $autresDepenses = [
            [
                'libelle' => 'Facture d\'électricité - Septembre 2024',
                'montant' => 150000,
                'type_depense' => 'electricite',
                'beneficiaire' => 'EDG (Électricité de Guinée)',
                'reference_facture' => 'FACT-EDG-2024-09',
                'statut' => 'paye',
                'mode_paiement' => 'cheque',
                'reference_paiement' => 'CHQ-EDG-001'
            ],
            [
                'libelle' => 'Facture d\'eau - Septembre 2024',
                'montant' => 75000,
                'type_depense' => 'eau',
                'beneficiaire' => 'SEEG (Société des Eaux de Guinée)',
                'reference_facture' => 'FACT-SEEG-2024-09',
                'statut' => 'approuve'
            ],
            [
                'libelle' => 'Achat de matériel scolaire',
                'montant' => 500000,
                'type_depense' => 'achat_materiel',
                'beneficiaire' => 'Fournisseur Matériel Scolaire',
                'reference_facture' => 'FACT-MAT-2024-001',
                'statut' => 'en_attente'
            ],
            [
                'libelle' => 'Maintenance des ordinateurs',
                'montant' => 200000,
                'type_depense' => 'maintenance',
                'beneficiaire' => 'Technicien Informatique',
                'statut' => 'paye',
                'mode_paiement' => 'especes'
            ],
            [
                'libelle' => 'Formation des enseignants',
                'montant' => 1000000,
                'type_depense' => 'formation',
                'beneficiaire' => 'Centre de Formation Pédagogique',
                'statut' => 'en_attente'
            ]
        ];

        foreach ($autresDepenses as $depenseData) {
            $depenseData['date_depense'] = now()->subDays(rand(1, 30));
            $depenseData['description'] = $depenseData['description'] ?? 'Dépense administrative';
            
            if (isset($depenseData['statut']) && $depenseData['statut'] === 'paye') {
                $depenseData['paye_par'] = $admin->id;
                $depenseData['date_paiement'] = $depenseData['date_depense'];
            }
            
            if (isset($depenseData['statut']) && $depenseData['statut'] === 'approuve') {
                $depenseData['approuve_par'] = $admin->id;
                $depenseData['date_approbation'] = $depenseData['date_depense'];
            }

            Depense::create($depenseData);
        }

        $this->command->info('Données de dépenses créées avec succès !');
        $this->command->info('- ' . $enseignants->count() . ' salaires d\'enseignants créés');
        $this->command->info('- ' . count($autresDepenses) . ' autres dépenses créées');
    }
}
