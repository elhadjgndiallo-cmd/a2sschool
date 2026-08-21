<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emplois_temps', function (Blueprint $table) {
            if (!Schema::hasColumn('emplois_temps', 'annee_scolaire_id')) {
                $table->unsignedBigInteger('annee_scolaire_id')->nullable()->after('enseignant_id');
                $table->foreign('annee_scolaire_id')
                    ->references('id')
                    ->on('annee_scolaires')
                    ->nullOnDelete();
                $table->index('annee_scolaire_id');
            }
        });

        // Rattacher d'abord via l'année de l'enseignant
        if (Schema::hasColumn('enseignants', 'annee_scolaire_id')) {
            DB::statement('
                UPDATE emplois_temps et
                INNER JOIN enseignants e ON e.id = et.enseignant_id
                SET et.annee_scolaire_id = e.annee_scolaire_id
                WHERE et.annee_scolaire_id IS NULL
                  AND e.annee_scolaire_id IS NOT NULL
            ');
        }

        // Le reste → année active
        $anneeActive = DB::table('annee_scolaires')->where('active', true)->first();
        if ($anneeActive) {
            DB::table('emplois_temps')
                ->whereNull('annee_scolaire_id')
                ->update(['annee_scolaire_id' => $anneeActive->id]);
        }
    }

    public function down(): void
    {
        Schema::table('emplois_temps', function (Blueprint $table) {
            if (Schema::hasColumn('emplois_temps', 'annee_scolaire_id')) {
                $table->dropForeign(['annee_scolaire_id']);
                $table->dropIndex(['annee_scolaire_id']);
                $table->dropColumn('annee_scolaire_id');
            }
        });
    }
};
