<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UtilisateurSeeder;
use Database\Seeders\ClasseSeeder;
use Database\Seeders\EleveSeeder;
use Database\Seeders\MatiereSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UtilisateurSeeder::class,
            ClasseSeeder::class,
            EleveSeeder::class,
            MatiereSeeder::class,
        ]);
    }
}
