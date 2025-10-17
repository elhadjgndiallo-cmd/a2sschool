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
        Schema::create('test_mensuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->integer('mois'); // 1-12
            $table->integer('annee'); // ex: 2025
            $table->decimal('note', 5, 2); // Note sur 20
            $table->integer('coefficient')->default(1);
            $table->foreignId('created_by')->constrained('utilisateurs')->onDelete('cascade');
            $table->timestamps();
            
            // Index pour éviter les doublons
            $table->unique(['eleve_id', 'matiere_id', 'mois', 'annee'], 'unique_test_mensuel');
            
            // Index pour les requêtes fréquentes
            $table->index(['classe_id', 'mois', 'annee']);
            $table->index(['eleve_id', 'mois', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_mensuels');
    }
};
