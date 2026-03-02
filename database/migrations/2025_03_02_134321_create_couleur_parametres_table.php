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
        Schema::create('couleur_parametres', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique(); // Clé unique pour identifier le paramètre
            $table->string('valeur'); // Valeur de la couleur (code hexadécimal)
            $table->string('description')->nullable(); // Description du paramètre
            $table->string('categorie')->default('general'); // Catégorie (general, bulletin, resultat, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couleur_parametres');
    }
};
