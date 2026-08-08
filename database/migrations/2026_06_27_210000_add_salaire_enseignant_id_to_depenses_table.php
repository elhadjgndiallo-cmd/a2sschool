<?php

use App\Models\Depense;
use App\Models\SalaireEnseignant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            if (!Schema::hasColumn('depenses', 'salaire_enseignant_id')) {
                $table->foreignId('salaire_enseignant_id')
                    ->nullable()
                    ->after('annee_scolaire_id')
                    ->constrained('salaires_enseignants')
                    ->nullOnDelete();
            }
        });

        $this->backfillSalaireLinks();
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            if (Schema::hasColumn('depenses', 'salaire_enseignant_id')) {
                $table->dropForeign(['salaire_enseignant_id']);
                $table->dropColumn('salaire_enseignant_id');
            }
        });
    }

    private function backfillSalaireLinks(): void
    {
        $salairesPayes = SalaireEnseignant::query()
            ->where('statut', 'payé')
            ->whereNotNull('date_paiement')
            ->get(['id', 'salaire_net', 'date_paiement']);

        foreach ($salairesPayes as $salaire) {
            $depense = Depense::query()
                ->where('type_depense', 'salaire_enseignant')
                ->whereNull('salaire_enseignant_id')
                ->where('montant', $salaire->salaire_net)
                ->whereDate('date_depense', $salaire->date_paiement)
                ->first();

            if ($depense) {
                $depense->update(['salaire_enseignant_id' => $salaire->id]);
            }
        }
    }
};
