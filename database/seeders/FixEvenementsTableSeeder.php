<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixEvenementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si la table evenements existe
        if (!Schema::hasTable('evenements')) {
            $this->command->info('Création de la table evenements...');
            
            Schema::create('evenements', function ($table) {
                $table->id();
                $table->string('titre', 100);
                $table->text('description')->nullable();
                $table->string('lieu', 100)->nullable();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->time('heure_debut')->nullable();
                $table->time('heure_fin')->nullable();
                $table->boolean('journee_entiere')->default(false);
                $table->enum('type', ['cours', 'examen', 'reunion', 'conge', 'autre'])->default('autre');
                $table->string('couleur', 7)->nullable()->default('#3788d8');
                $table->boolean('public')->default(true);
                $table->unsignedBigInteger('classe_id')->nullable();
                $table->unsignedBigInteger('createur_id');
                $table->integer('rappel')->nullable()->comment('Rappel en minutes avant l\'événement');
                $table->timestamps();
                
                // Index pour améliorer les performances
                $table->index('date_debut');
                $table->index('date_fin');
                $table->index('type');
                $table->index('public');
                $table->index('classe_id');
                $table->index('createur_id');
            });
            
            $this->command->info('Table evenements créée avec succès.');
        } else {
            $this->command->info('Table evenements existe déjà.');
        }
        
        // Ajouter les contraintes de clés étrangères si les tables référencées existent
        if (Schema::hasTable('evenements')) {
            $this->command->info('Vérification des contraintes de clés étrangères...');
            
            // Vérifier et ajouter la contrainte pour classe_id
            if (Schema::hasTable('classes')) {
                try {
                    DB::statement('ALTER TABLE evenements ADD CONSTRAINT evenements_classe_id_foreign FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE');
                    $this->command->info('Contrainte classe_id ajoutée.');
                } catch (\Exception $e) {
                    $this->command->warn('Contrainte classe_id déjà existante ou erreur: ' . $e->getMessage());
                }
            }
            
            // Vérifier et ajouter la contrainte pour createur_id
            if (Schema::hasTable('utilisateurs')) {
                try {
                    DB::statement('ALTER TABLE evenements ADD CONSTRAINT evenements_createur_id_foreign FOREIGN KEY (createur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE');
                    $this->command->info('Contrainte createur_id ajoutée.');
                } catch (\Exception $e) {
                    $this->command->warn('Contrainte createur_id déjà existante ou erreur: ' . $e->getMessage());
                }
            }
        }
        
        $this->command->info('Fix de la table evenements terminé.');
    }
}
