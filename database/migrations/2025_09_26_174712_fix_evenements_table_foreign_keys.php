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
        // Vérifier si la table evenements existe
        if (Schema::hasTable('evenements')) {
            // Supprimer les contraintes de clés étrangères existantes si elles existent
            Schema::table('evenements', function (Blueprint $table) {
                // Supprimer les contraintes de clés étrangères
                $table->dropForeign(['classe_id']);
                $table->dropForeign(['createur_id']);
            });
            
            // Recréer les contraintes de clés étrangères avec vérification d'existence des tables
            Schema::table('evenements', function (Blueprint $table) {
                // Vérifier que les tables référencées existent avant de créer les contraintes
                if (Schema::hasTable('classes')) {
                    $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
                }
                
                if (Schema::hasTable('utilisateurs')) {
                    $table->foreign('createur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                }
            });
        } else {
            // Si la table n'existe pas, la créer sans contraintes de clés étrangères
            Schema::create('evenements', function (Blueprint $table) {
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
                
                // Index pour améliorer les performances des requêtes fréquentes
                $table->index('date_debut');
                $table->index('date_fin');
                $table->index('type');
                $table->index('public');
                $table->index('classe_id');
                $table->index('createur_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};