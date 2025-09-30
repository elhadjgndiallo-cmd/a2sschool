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
        // Vérifier que la table evenements existe
        if (Schema::hasTable('evenements')) {
            Schema::table('evenements', function (Blueprint $table) {
                // Ajouter les contraintes de clés étrangères seulement si les tables référencées existent
                if (Schema::hasTable('classes')) {
                    $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
                }
                
                if (Schema::hasTable('utilisateurs')) {
                    $table->foreign('createur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('evenements')) {
            // Vérifier l'existence des clés étrangères avant de les supprimer
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'evenements' 
                AND CONSTRAINT_NAME LIKE '%_foreign'
            ");
            
            $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');
            
            // Supprimer seulement les clés étrangères qui existent
            if (!empty($existingForeignKeys)) {
                Schema::table('evenements', function (Blueprint $table) use ($existingForeignKeys) {
                    if (in_array('evenements_classe_id_foreign', $existingForeignKeys)) {
                        $table->dropForeign(['classe_id']);
                    }
                    
                    if (in_array('evenements_createur_id_foreign', $existingForeignKeys)) {
                        $table->dropForeign(['createur_id']);
                    }
                });
            }
        }
    }
};