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
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            
            // Informations générales
            $table->string('nom');
            $table->text('adresse');
            $table->string('telephone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('slogan')->nullable();
            $table->string('logo')->nullable(); // Chemin vers l'image du logo
            $table->string('cachet')->nullable(); // Chemin vers l'image du cachet
            $table->text('description')->nullable();
            
            // Responsabilités
            $table->string('dg')->nullable(); // Directeur Général
            $table->string('directeur_primaire')->nullable();
            $table->string('prefixe_matricule', 10)->nullable(); // Ex: "2025DIAKAD"
            $table->string('suffixe_matricule', 10)->nullable(); // Ex: "001"
            $table->enum('statut_etablissement', ['prive', 'public', 'semi_prive'])->default('prive');
            
            // Statut actif
            $table->boolean('actif')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
