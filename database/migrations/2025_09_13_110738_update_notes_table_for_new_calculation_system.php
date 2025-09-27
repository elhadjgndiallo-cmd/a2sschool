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
        Schema::table('notes', function (Blueprint $table) {
            // Ajouter les nouveaux champs seulement s'ils n'existent pas déjà
            if (!Schema::hasColumn('notes', 'note_cours')) {
                $table->decimal('note_cours', 5, 2)->nullable()->after('enseignant_id');
            }
            if (!Schema::hasColumn('notes', 'note_composition')) {
                $table->decimal('note_composition', 5, 2)->nullable()->after('note_cours');
            }
            if (!Schema::hasColumn('notes', 'note_finale')) {
                $table->decimal('note_finale', 5, 2)->nullable()->after('note_composition');
            }
            
            // Modifier la colonne période pour ne garder que 2 trimestres
            // Vérifier d'abord si la colonne existe et a les bonnes valeurs
            if (Schema::hasColumn('notes', 'periode')) {
                $table->enum('periode', ['trimestre1', 'trimestre2'])->default('trimestre1')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Supprimer les nouveaux champs seulement s'ils existent
            $columnsToDrop = [];
            if (Schema::hasColumn('notes', 'note_cours')) $columnsToDrop[] = 'note_cours';
            if (Schema::hasColumn('notes', 'note_composition')) $columnsToDrop[] = 'note_composition';
            if (Schema::hasColumn('notes', 'note_finale')) $columnsToDrop[] = 'note_finale';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Restaurer l'ancienne colonne période
            if (Schema::hasColumn('notes', 'periode')) {
                $table->enum('periode', ['trimestre1', 'trimestre2', 'trimestre3', 'semestre1', 'semestre2'])->default('trimestre1')->change();
            }
        });
    }
};
