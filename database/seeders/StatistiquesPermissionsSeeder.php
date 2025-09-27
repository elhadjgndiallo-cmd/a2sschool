<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonnelAdministration;
use Illuminate\Support\Facades\DB;

class StatistiquesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions pour les statistiques
        $statistiquesPermissions = [
            'statistiques.view',
            'statistiques.financieres',
            'statistiques.absences'
        ];

        // Mettre à jour les comptes administrateurs existants pour ajouter les permissions de statistiques
        $personnelAdmin = PersonnelAdministration::all();

        foreach ($personnelAdmin as $personnel) {
            // S'assurer que les permissions sont un tableau
            $currentPermissions = $personnel->permissions ?? [];
            if (is_string($currentPermissions)) {
                $currentPermissions = json_decode($currentPermissions, true) ?? [];
            }
            
            // Ajouter les permissions de statistiques selon le poste
            switch ($personnel->poste) {
                case 'Comtable':
                case 'Comptable':
                    // Les comptables peuvent voir toutes les statistiques
                    $newPermissions = array_merge($currentPermissions, $statistiquesPermissions);
                    break;
                    
                case 'Directeur':
                case 'Directeur Adjoint':
                    // Les directeurs peuvent voir toutes les statistiques
                    $newPermissions = array_merge($currentPermissions, $statistiquesPermissions);
                    break;
                    
                case 'Gestionnaire Paiements':
                    // Les gestionnaires de paiements peuvent voir les statistiques financières
                    $newPermissions = array_merge($currentPermissions, ['statistiques.view', 'statistiques.financieres']);
                    break;
                    
                case 'Surveillant':
                case 'Surveillant Général':
                    // Les surveillants peuvent voir les statistiques d'absences
                    $newPermissions = array_merge($currentPermissions, ['statistiques.view', 'statistiques.absences']);
                    break;
                    
                default:
                    // Par défaut, seulement les statistiques générales
                    $newPermissions = array_merge($currentPermissions, ['statistiques.view']);
                    break;
            }

            // Supprimer les doublons
            $newPermissions = array_unique($newPermissions);
            
            $personnel->update(['permissions' => $newPermissions]);
        }

        $this->command->info('Permissions de statistiques ajoutées avec succès !');
    }
}