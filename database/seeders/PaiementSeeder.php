<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FraisScolarite;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\Utilisateur;

class PaiementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer quelques élèves
        $eleves = Eleve::limit(5)->get();
        
        if ($eleves->count() == 0) {
            $this->command->warn('Aucun élève trouvé. Veuillez d\'abord exécuter EleveSeeder.');
            return;
        }

        // Récupérer un utilisateur admin pour les paiements
        $admin = Utilisateur::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('Aucun utilisateur admin trouvé. Veuillez d\'abord exécuter UtilisateurSeeder.');
            return;
        }

        // Créer des frais de scolarité avec paiement par tranches
        foreach ($eleves as $eleve) {
            // Frais de scolarité par tranches (3 tranches trimestrielles)
            $fraisTranches = FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais de scolarité 2024-2025',
                'montant' => 150000,
                'date_echeance' => now()->addMonths(9),
                'statut' => 'en_attente',
                'type_frais' => 'scolarite',
                'description' => 'Frais de scolarité annuels payables par tranches',
                'paiement_par_tranches' => true,
                'nombre_tranches' => 3,
                'montant_tranche' => 50000,
                'periode_tranche' => 'trimestriel',
                'date_debut_tranches' => now()->addMonth()
            ]);

            // Créer les tranches
            $fraisTranches->creerTranchesPaiement();

            // Simuler le paiement de la première tranche
            $premiereTranche = $fraisTranches->tranchesPaiement()->first();
            if ($premiereTranche) {
                Paiement::create([
                    'frais_scolarite_id' => $fraisTranches->id,
                    'tranche_paiement_id' => $premiereTranche->id,
                    'montant_paye' => 50000,
                    'date_paiement' => now()->subDays(10),
                    'mode_paiement' => 'especes',
                    'encaisse_par' => $admin->id
                ]);

                $premiereTranche->update([
                    'montant_paye' => 50000,
                    'date_paiement' => now()->subDays(10),
                    'statut' => 'paye'
                ]);
            }

            // Frais d'inscription (paiement direct)
            $fraisInscription = FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais d\'inscription 2024-2025',
                'montant' => 25000,
                'date_echeance' => now()->subMonth(),
                'statut' => 'paye',
                'type_frais' => 'inscription',
                'description' => 'Frais d\'inscription pour l\'année scolaire',
                'paiement_par_tranches' => false
            ]);

            // Paiement de l'inscription
            Paiement::create([
                'frais_scolarite_id' => $fraisInscription->id,
                'montant_paye' => 25000,
                'date_paiement' => now()->subMonth(),
                'mode_paiement' => 'cheque',
                'reference_paiement' => 'CHQ-2024-001',
                'encaisse_par' => $admin->id
            ]);

            // Frais de cantine (paiement mensuel)
            $fraisCantine = FraisScolarite::create([
                'eleve_id' => $eleve->id,
                'libelle' => 'Frais de cantine - Septembre 2024',
                'montant' => 15000,
                'date_echeance' => now()->addDays(5),
                'statut' => 'en_attente',
                'type_frais' => 'cantine',
                'description' => 'Frais de cantine pour le mois de septembre',
                'paiement_par_tranches' => false
            ]);
        }

        $this->command->info('Données de paiement créées avec succès !');
    }
}
