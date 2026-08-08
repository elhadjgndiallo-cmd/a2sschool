<?php

use App\Models\AnneeScolaire;
use App\Models\Depense;
use App\Models\Entree;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            if (!Schema::hasColumn('entrees', 'annee_scolaire_id')) {
                $table->foreignId('annee_scolaire_id')
                    ->nullable()
                    ->after('enregistre_par')
                    ->constrained('annee_scolaires')
                    ->nullOnDelete();
            }
        });

        Schema::table('depenses', function (Blueprint $table) {
            if (!Schema::hasColumn('depenses', 'annee_scolaire_id')) {
                $table->foreignId('annee_scolaire_id')
                    ->nullable()
                    ->after('observations')
                    ->constrained('annee_scolaires')
                    ->nullOnDelete();
            }
        });

        $this->backfillAnneeScolaire(Entree::class, 'date_entree');
        $this->backfillAnneeScolaire(Depense::class, 'date_depense');
    }

    public function down(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            if (Schema::hasColumn('entrees', 'annee_scolaire_id')) {
                $table->dropForeign(['annee_scolaire_id']);
                $table->dropColumn('annee_scolaire_id');
            }
        });

        Schema::table('depenses', function (Blueprint $table) {
            if (Schema::hasColumn('depenses', 'annee_scolaire_id')) {
                $table->dropForeign(['annee_scolaire_id']);
                $table->dropColumn('annee_scolaire_id');
            }
        });
    }

    private function backfillAnneeScolaire(string $modelClass, string $dateColumn): void
    {
        $annees = AnneeScolaire::orderBy('date_debut')->get();
        $activeId = AnneeScolaire::anneeActive()?->id;

        $modelClass::query()
            ->whereNull('annee_scolaire_id')
            ->orderBy('id')
            ->each(function ($record) use ($annees, $activeId, $dateColumn) {
                $date = $record->{$dateColumn};
                $matched = null;

                if ($date) {
                    $matched = $annees->first(
                        fn (AnneeScolaire $annee) => $date >= $annee->date_debut && $date <= $annee->date_fin
                    );
                }

                $record->update([
                    'annee_scolaire_id' => $matched?->id ?? $activeId,
                ]);
            });
    }
};
