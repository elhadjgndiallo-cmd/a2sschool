<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum type_frais pour ajouter 'reinscription'
        DB::statement("ALTER TABLE frais_scolarite MODIFY COLUMN type_frais ENUM('inscription', 'reinscription', 'scolarite', 'cantine', 'transport', 'activites', 'autre') DEFAULT 'scolarite'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'reinscription' de l'enum
        DB::statement("ALTER TABLE frais_scolarite MODIFY COLUMN type_frais ENUM('inscription', 'scolarite', 'cantine', 'transport', 'activites', 'autre') DEFAULT 'scolarite'");
    }
};
