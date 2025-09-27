<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SalaireEnseignant;
use App\Models\Enseignant;
use App\Models\Utilisateur;

class SalaireEnseignantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a des enseignants
        $enseignants = Enseignant::all();
        if ($enseignants->isEmpty()) {
            $this->command->error('Aucun enseignant trouvé. Veuillez d\'abord exécuter EnseignantSeeder.');
            return;
        }

        // Vérifier qu'il y a des utilisateurs pour les calculs
        $admin = Utilisateur::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Aucun administrateur trouvé. Veuillez d\'abord exécuter UtilisateurSeeder.');
            return;
        }

        $this->command->info('Création des salaires des enseignants...');

        // Créer des salaires pour les 3 derniers mois
        $moisActuel = now();
        
        foreach ($enseignants as $enseignant) {
            // Salaire du mois actuel
            $this->creerSalaire($enseignant, $moisActuel, $admin->id);
            
            // Salaire du mois précédent
            $moisPrecedent = $moisActuel->copy()->subMonth();
            $this->creerSalaire($enseignant, $moisPrecedent, $admin->id);
            
            // Salaire d'il y a 2 mois
            $moisAvantDernier = $moisActuel->copy()->subMonths(2);
            $this->creerSalaire($enseignant, $moisAvantDernier, $admin->id);
        }

        $this->command->info('Salaires des enseignants créés avec succès !');
    }

    private function creerSalaire($enseignant, $mois, $adminId)
    {
        // Vérifier qu'il n'y a pas déjà un salaire pour cette période
        $debutMois = $mois->copy()->startOfMonth();
        $finMois = $mois->copy()->endOfMonth();
        
        $existingSalaire = SalaireEnseignant::where('enseignant_id', $enseignant->id)
            ->where('periode_debut', $debutMois->toDateString())
            ->where('periode_fin', $finMois->toDateString())
            ->first();

        if ($existingSalaire) {
            return; // Salire déjà existant
        }

        // Paramètres variables selon l'enseignant
        $nombreHeures = rand(60, 100); // Entre 60 et 100 heures
        $tauxHoraire = rand(5000, 15000); // Entre 5000 et 15000 GNF/heure
        $salaireBase = rand(200000, 500000); // Entre 200k et 500k GNF
        
        // Primes variables
        $primeAnciennete = rand(0, 100000); // Prime d'ancienneté
        $primePerformance = rand(0, 50000); // Prime de performance
        $primeHeuresSupp = rand(0, 80000); // Heures supplémentaires
        
        // Déductions variables
        $deductionAbsences = rand(0, 50000); // Déduction absences
        $deductionAutres = rand(0, 30000); // Autres déductions

        // Calculer les totaux
        $salaireHoraire = $nombreHeures * $tauxHoraire;
        $salaireBrut = $salaireBase + $salaireHoraire + $primeAnciennete + $primePerformance + $primeHeuresSupp;
        $salaireNet = $salaireBrut - $deductionAbsences - $deductionAutres;

        // Déterminer le statut selon le mois
        $statut = 'calculé';
        if ($mois->isPast()) {
            $statut = rand(0, 1) ? 'validé' : 'payé';
        }

        $salaire = SalaireEnseignant::create([
            'enseignant_id' => $enseignant->id,
            'periode_debut' => $debutMois->toDateString(),
            'periode_fin' => $finMois->toDateString(),
            'nombre_heures' => $nombreHeures,
            'taux_horaire' => $tauxHoraire,
            'salaire_base' => $salaireBase,
            'prime_anciennete' => $primeAnciennete,
            'prime_performance' => $primePerformance,
            'prime_heures_supplementaires' => $primeHeuresSupp,
            'deduction_absences' => $deductionAbsences,
            'deduction_autres' => $deductionAutres,
            'salaire_brut' => $salaireBrut,
            'salaire_net' => $salaireNet,
            'statut' => $statut,
            'observations' => 'Salaire généré automatiquement pour ' . $mois->format('F Y'),
            'calcule_par' => $adminId,
            'date_calcul' => $debutMois->addDays(rand(1, 5))->toDateString()
        ]);

        // Si le salaire est validé ou payé, ajouter les dates correspondantes
        if ($statut === 'validé' || $statut === 'payé') {
            $salaire->update([
                'valide_par' => $adminId,
                'date_validation' => $debutMois->addDays(rand(6, 10))->toDateString()
            ]);
        }

        if ($statut === 'payé') {
            $salaire->update([
                'paye_par' => $adminId,
                'date_paiement' => $debutMois->addDays(rand(11, 15))->toDateString()
            ]);
        }

        $this->command->info("Salaire créé pour {$enseignant->nom} {$enseignant->prenom} - {$mois->format('F Y')} - {$statut}");
    }
}
