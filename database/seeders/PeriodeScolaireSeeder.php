<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeriodeScolaire;

class PeriodeScolaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodes = [
            [
                'nom' => 'Trimestre 1',
                'date_debut' => '2024-09-15',
                'date_fin' => '2024-12-15',
                'date_conseil' => '2024-12-20',
                'couleur' => 'primary',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'nom' => 'Trimestre 2',
                'date_debut' => '2025-01-05',
                'date_fin' => '2025-03-25',
                'date_conseil' => '2025-03-30',
                'couleur' => 'success',
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'nom' => 'Trimestre 3',
                'date_debut' => '2025-04-15',
                'date_fin' => '2025-06-30',
                'date_conseil' => '2025-07-05',
                'couleur' => 'warning',
                'actif' => true,
                'ordre' => 3,
            ],
        ];

        foreach ($periodes as $periode) {
            PeriodeScolaire::updateOrCreate(
                ['nom' => $periode['nom']],
                $periode
            );
        }
    }
}