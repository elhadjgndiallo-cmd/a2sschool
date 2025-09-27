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
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Ex: Mathématiques, Français, Histoire
            $table->string('code')->unique(); // Ex: MATH, FR, HIST
            $table->text('description')->nullable();
            $table->integer('coefficient')->default(1);
            $table->string('couleur')->default('#3498db'); // Pour l'affichage dans l'emploi du temps
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};
