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
        Schema::create('recus_rappel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('frais_scolarite_id')->constrained('frais_scolarite')->onDelete('cascade');
            $table->decimal('montant_total_du', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('montant_restant', 10, 2);
            $table->decimal('montant_a_payer', 10, 2)->nullable(); // Montant que le comptable écrit manuellement
            $table->date('date_rappel');
            $table->date('date_echeance');
            $table->enum('statut', ['actif', 'expire', 'paye'])->default('actif');
            $table->text('observations')->nullable();
            $table->foreignId('genere_par')->constrained('utilisateurs');
            $table->string('numero_recu_rappel')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recus_rappel');
    }
};
