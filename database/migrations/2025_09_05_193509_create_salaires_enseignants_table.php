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
        Schema::create('salaires_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            $table->date('periode_debut'); // Début de la période de salaire (ex: 1er du mois)
            $table->date('periode_fin'); // Fin de la période de salaire (ex: 31 du mois)
            $table->integer('nombre_heures')->default(0); // Nombre d'heures enseignées
            $table->decimal('taux_horaire', 8, 2)->default(0); // Taux horaire en GNF
            $table->decimal('salaire_base', 10, 2)->default(0); // Salaire de base
            $table->decimal('prime_anciennete', 10, 2)->default(0); // Prime d'ancienneté
            $table->decimal('prime_performance', 10, 2)->default(0); // Prime de performance
            $table->decimal('prime_heures_supplementaires', 10, 2)->default(0); // Heures supplémentaires
            $table->decimal('deduction_absences', 10, 2)->default(0); // Déduction pour absences
            $table->decimal('deduction_autres', 10, 2)->default(0); // Autres déductions
            $table->decimal('salaire_brut', 10, 2)->default(0); // Salaire brut calculé
            $table->decimal('salaire_net', 10, 2)->default(0); // Salaire net final
            $table->enum('statut', ['calculé', 'validé', 'payé', 'annulé'])->default('calculé');
            $table->text('observations')->nullable(); // Observations sur le calcul
            $table->foreignId('calcule_par')->nullable()->constrained('utilisateurs'); // Qui a calculé
            $table->foreignId('valide_par')->nullable()->constrained('utilisateurs'); // Qui a validé
            $table->foreignId('paye_par')->nullable()->constrained('utilisateurs'); // Qui a payé
            $table->date('date_calcul')->nullable();
            $table->date('date_validation')->nullable();
            $table->date('date_paiement')->nullable();
            $table->timestamps();
            
            $table->unique(['enseignant_id', 'periode_debut', 'periode_fin'], 'unique_salaire_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaires_enseignants');
    }
};
