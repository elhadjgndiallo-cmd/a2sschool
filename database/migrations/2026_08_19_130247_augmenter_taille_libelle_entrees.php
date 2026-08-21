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
        Schema::table('entrees', function (Blueprint $table) {
            // Augmenter la taille de la colonne libelle de VARCHAR(255) à TEXT
            // pour supporter les factures avec 6+ lignes (les libellés deviennent très longs)
            $table->text('libelle')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            // Revenir à VARCHAR(255) si rollback
            $table->string('libelle', 255)->change();
        });
    }
};
