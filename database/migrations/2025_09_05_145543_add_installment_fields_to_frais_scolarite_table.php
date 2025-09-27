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
        Schema::table('frais_scolarite', function (Blueprint $table) {
            // Champs pour la gestion par tranches
            $table->boolean('paiement_par_tranches')->default(false);
            $table->integer('nombre_tranches')->nullable(); // Nombre de tranches (ex: 3 pour trimestre)
            $table->decimal('montant_tranche', 10, 2)->nullable(); // Montant par tranche
            $table->enum('periode_tranche', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])->nullable();
            $table->date('date_debut_tranches')->nullable(); // Date de début des paiements
            $table->json('calendrier_tranches')->nullable(); // Calendrier des échéances
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_scolarite', function (Blueprint $table) {
            $table->dropColumn([
                'paiement_par_tranches',
                'nombre_tranches',
                'montant_tranche',
                'periode_tranche',
                'date_debut_tranches',
                'calendrier_tranches'
            ]);
        });
    }
};
