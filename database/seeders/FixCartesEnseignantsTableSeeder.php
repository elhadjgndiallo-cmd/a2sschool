<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCartesEnseignantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si la table cartes_enseignants existe
        if (!Schema::hasTable('cartes_enseignants')) {
            $this->command->info('Création de la table cartes_enseignants...');
            
            Schema::create('cartes_enseignants', function ($table) {
                $table->id();
                $table->unsignedBigInteger('enseignant_id');
                $table->string('numero_carte')->unique();
                $table->date('date_emission');
                $table->date('date_expiration');
                $table->enum('statut', ['active', 'expiree', 'suspendue', 'annulee'])->default('active');
                $table->enum('type_carte', ['standard', 'temporaire', 'remplacement'])->default('standard');
                $table->string('photo_path')->nullable();
                $table->text('qr_code')->nullable();
                $table->text('observations')->nullable();
                $table->unsignedBigInteger('emise_par');
                $table->unsignedBigInteger('validee_par')->nullable();
                $table->timestamps();
            });
            
            $this->command->info('Table cartes_enseignants créée avec succès.');
        } else {
            $this->command->info('Table cartes_enseignants existe déjà.');
        }
        
        // Ajouter les contraintes de clés étrangères si les tables référencées existent
        if (Schema::hasTable('cartes_enseignants')) {
            $this->command->info('Vérification des contraintes de clés étrangères...');
            
            // Vérifier et ajouter la contrainte pour enseignant_id
            if (Schema::hasTable('enseignants')) {
                try {
                    DB::statement('ALTER TABLE cartes_enseignants ADD CONSTRAINT cartes_enseignants_enseignant_id_foreign FOREIGN KEY (enseignant_id) REFERENCES enseignants(id) ON DELETE CASCADE');
                    $this->command->info('Contrainte enseignant_id ajoutée.');
                } catch (\Exception $e) {
                    $this->command->warn('Contrainte enseignant_id déjà existante ou erreur: ' . $e->getMessage());
                }
            }
            
            // Vérifier et ajouter la contrainte pour emise_par
            if (Schema::hasTable('utilisateurs')) {
                try {
                    DB::statement('ALTER TABLE cartes_enseignants ADD CONSTRAINT cartes_enseignants_emise_par_foreign FOREIGN KEY (emise_par) REFERENCES utilisateurs(id) ON DELETE SET NULL');
                    $this->command->info('Contrainte emise_par ajoutée.');
                } catch (\Exception $e) {
                    $this->command->warn('Contrainte emise_par déjà existante ou erreur: ' . $e->getMessage());
                }
                
                try {
                    DB::statement('ALTER TABLE cartes_enseignants ADD CONSTRAINT cartes_enseignants_validee_par_foreign FOREIGN KEY (validee_par) REFERENCES utilisateurs(id) ON DELETE SET NULL');
                    $this->command->info('Contrainte validee_par ajoutée.');
                } catch (\Exception $e) {
                    $this->command->warn('Contrainte validee_par déjà existante ou erreur: ' . $e->getMessage());
                }
            }
        }
        
        $this->command->info('Fix de la table cartes_enseignants terminé.');
    }
}