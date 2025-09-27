<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FraisScolarite;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Models\Utilisateur;

class ExemplePaiement10emeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les élèves de la 10ème année
        $eleves10eme = Eleve::whereHas('classe', function($query) {
            $query->where('nom', 'like', '%10%');
        })->get();
        
        if ($eleves10eme->count() == 0) {
            $this->command->warn('Aucun élève de 10ème année trouvé. Création d\'un exemple...');
            // Créer un élève exemple pour la 10ème année
            $eleve = Eleve::first();
            if ($eleve) {
                $eleves10eme = collect([$eleve]);
            } else {
                $this->command->error('Aucun élève trouvé dans la base de données.');
                return;
            }
        }

        // Récupérer un utilisateur admin pour les paiements
        $admin = Utilisateur::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Aucun utilisateur admin trouvé.');
            return;
        }

        foreach ($eleves10eme as $eleve) {
            // Créer les frais de scolarité pour la 10ème année - 250,000 GNF par mois (12 mois)
            $fraisScolarite10eme = FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais de scolarité 10ème année 2024-2025',
                'montant' => 3000000, // 250,000 × 12 mois
                'date_echeance' => now()->addMonths(12),
                'statut' => 'en_attente',
                'type_frais' => 'scolarite',
                'description' => 'Frais de scolarité mensuels pour la 10ème année - 250,000 GNF par mois',
                'paiement_par_tranches' => true,
                'nombre_tranches' => 12,
                'montant_tranche' => 250000,
                'periode_tranche' => 'mensuel',
                'date_debut_tranches' => now()->addMonth()
            ]);

            // Créer les 12 tranches mensuelles
            $fraisScolarite10eme->creerTranchesPaiement();

            // Simuler le paiement des 3 premiers mois
            $tranches = $fraisScolarite10eme->tranchesPaiement()->orderBy('numero_tranche')->take(3)->get();
            
            foreach ($tranches as $index => $tranche) {
                $datePaiement = now()->subMonths(3 - $index);
                
                Paiement::create([
                    'frais_scolarite_id' => $fraisScolarite10eme->id,
                    'tranche_paiement_id' => $tranche->id,
                    'montant_paye' => 250000,
                    'date_paiement' => $datePaiement,
                    'mode_paiement' => $index == 0 ? 'especes' : ($index == 1 ? 'cheque' : 'virement'),
                    'reference_paiement' => $index == 1 ? 'CHQ-10EME-001' : ($index == 2 ? 'VIR-10EME-001' : null),
                    'observations' => "Paiement du mois " . $tranche->numero_tranche . " - 10ème année",
                    'encaisse_par' => $admin->id
                ]);

                $tranche->update([
                    'montant_paye' => 250000,
                    'date_paiement' => $datePaiement,
                    'statut' => 'paye'
                ]);
            }

            // Créer aussi les frais d'inscription pour la 10ème année
            $fraisInscription10eme = FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais d\'inscription 10ème année 2024-2025',
                'montant' => 500000, // 500,000 GNF d'inscription
                'date_echeance' => now()->subMonth(),
                'statut' => 'paye',
                'type_frais' => 'inscription',
                'description' => 'Frais d\'inscription pour la 10ème année',
                'paiement_par_tranches' => false
            ]);

            // Paiement de l'inscription
            Paiement::create([
                'frais_scolarite_id' => $fraisInscription10eme->id,
                'montant_paye' => 500000,
                'date_paiement' => now()->subMonth(),
                'mode_paiement' => 'cheque',
                'reference_paiement' => 'CHQ-INS-10EME-001',
                'observations' => 'Paiement frais d\'inscription 10ème année',
                'encaisse_par' => $admin->id
            ]);
        }

        $this->command->info('Exemple de paiement 10ème année créé avec succès !');
        $this->command->info('- Frais de scolarité : 250,000 GNF/mois × 12 mois = 3,000,000 GNF');
        $this->command->info('- Frais d\'inscription : 500,000 GNF');
        $this->command->info('- 3 premiers mois déjà payés');
    }
}
