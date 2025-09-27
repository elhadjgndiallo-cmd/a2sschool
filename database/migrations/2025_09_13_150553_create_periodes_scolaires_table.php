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
        if (!Schema::hasTable('periodes_scolaires')) {
            Schema::create('periodes_scolaires', function (Blueprint $table) {
                $table->id();
                $table->string('nom'); // Trimestre 1, Trimestre 2, Trimestre 3
                $table->date('date_debut');
                $table->date('date_fin');
                $table->date('date_conseil');
                $table->string('couleur')->default('primary'); // primary, success, warning, danger, info
                $table->boolean('actif')->default(true);
                $table->integer('ordre')->default(1); // Ordre d'affichage
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodes_scolaires');
    }
};