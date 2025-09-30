<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            Schema::table('evenements', function (Blueprint $table) {
                try {
                    $table->dropForeign(['classe_id']);
                } catch (Exception $e) {
                    // La clé étrangère n'existe pas, continuer
                }
                
                try {
                    $table->dropForeign(['createur_id']);
                } catch (Exception $e) {
                    // La clé étrangère n'existe pas, continuer
                }
            });
        }
    }
};