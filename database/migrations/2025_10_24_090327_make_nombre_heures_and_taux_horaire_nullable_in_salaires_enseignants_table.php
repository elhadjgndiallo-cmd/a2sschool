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
        Schema::table('salaires_enseignants', function (Blueprint $table) {
            $table->integer('nombre_heures')->nullable()->change();
            $table->decimal('taux_horaire', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaires_enseignants', function (Blueprint $table) {
            $table->integer('nombre_heures')->default(0)->change();
            $table->decimal('taux_horaire', 8, 2)->default(0)->change();
        });
    }
};
