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
        // Vérifier que la table cartes_enseignants existe
        if (Schema::hasTable('cartes_enseignants')) {
            Schema::table('cartes_enseignants', function (Blueprint $table) {
                // Ajouter les contraintes de clés étrangères seulement si les tables référencées existent
                if (Schema::hasTable('enseignants')) {
                    $table->foreign('enseignant_id')->references('id')->on('enseignants')->onDelete('cascade');
                }
                
                if (Schema::hasTable('utilisateurs')) {
                    $table->foreign('emise_par')->references('id')->on('utilisateurs')->onDelete('set null');
                    $table->foreign('validee_par')->references('id')->on('utilisateurs')->onDelete('set null');
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
            Schema::table('cartes_enseignants', function (Blueprint $table) {
                try {
                    $table->dropForeign(['enseignant_id']);
                } catch (Exception $e) {
                    // La clé étrangère n'existe pas, continuer
                }
                
                try {
                    $table->dropForeign(['emise_par']);
                } catch (Exception $e) {
                    // La clé étrangère n'existe pas, continuer
                }
                
                try {
                    $table->dropForeign(['validee_par']);
                } catch (Exception $e) {
                    // La clé étrangère n'existe pas, continuer
                }
            });
        }
    }
};