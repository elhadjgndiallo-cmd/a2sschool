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
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->string('numero_etudiant')->unique();
            $table->date('date_inscription');
            $table->enum('statut', ['inscrit', 'en_cours', 'diplome', 'abandonne'])->default('inscrit');
            $table->string('niveau_precedent')->nullable();
            $table->string('etablissement_precedent')->nullable();
            $table->text('observations')->nullable();
            $table->json('documents')->nullable(); // Stockage des chemins des documents
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
