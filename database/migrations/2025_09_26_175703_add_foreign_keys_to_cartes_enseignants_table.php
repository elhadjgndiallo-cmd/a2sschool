<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier que la table cartes_enseignants existe
        if (Schema::hasTable('cartes_enseignants')) {
            // Vérifier l'existence des clés étrangères avant de les créer
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'cartes_enseignants' 
                AND CONSTRAINT_NAME LIKE '%_foreign'
            ");
            
            $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');
            
            Schema::table('cartes_enseignants', function (Blueprint $table) use ($existingForeignKeys) {
                // Ajouter les contraintes de clés étrangères seulement si elles n'existent pas déjà
                if (Schema::hasTable('enseignants') && !in_array('cartes_enseignants_enseignant_id_foreign', $existingForeignKeys)) {
                    $table->foreign('enseignant_id')->references('id')->on('enseignants')->onDelete('cascade');
                }
                
                if (Schema::hasTable('utilisateurs')) {
                    if (!in_array('cartes_enseignants_emise_par_foreign', $existingForeignKeys)) {
                        $table->foreign('emise_par')->references('id')->on('utilisateurs')->onDelete('set null');
                    }
                    
                    if (!in_array('cartes_enseignants_validee_par_foreign', $existingForeignKeys)) {
                        $table->foreign('validee_par')->references('id')->on('utilisateurs')->onDelete('set null');
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cartes_enseignants')) {
            // Vérifier l'existence des clés étrangères avant de les supprimer
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'cartes_enseignants' 
                AND CONSTRAINT_NAME LIKE '%_foreign'
            ");
            
            $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');
            
            // Supprimer seulement les clés étrangères qui existent
            if (!empty($existingForeignKeys)) {
                Schema::table('cartes_enseignants', function (Blueprint $table) use ($existingForeignKeys) {
                    if (in_array('cartes_enseignants_enseignant_id_foreign', $existingForeignKeys)) {
                        $table->dropForeign(['enseignant_id']);
                    }
                    
                    if (in_array('cartes_enseignants_emise_par_foreign', $existingForeignKeys)) {
                        $table->dropForeign(['emise_par']);
                    }
                    
                    if (in_array('cartes_enseignants_validee_par_foreign', $existingForeignKeys)) {
                        $table->dropForeign(['validee_par']);
                    }
                });
            }
        }
    }
};