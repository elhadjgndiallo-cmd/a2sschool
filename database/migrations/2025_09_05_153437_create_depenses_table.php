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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('libelle'); // Ex: Salaire professeur, Achat matériel, etc.
            $table->decimal('montant', 12, 2); // Montant de la dépense
            $table->date('date_depense'); // Date de la dépense
            $table->enum('type_depense', [
                'salaire_enseignant', 
                'salaire_personnel', 
                'achat_materiel', 
                'maintenance', 
                'electricite', 
                'eau', 
                'nourriture', 
                'transport', 
                'communication', 
                'formation', 
                'autre'
            ])->default('autre');
            $table->enum('statut', ['en_attente', 'approuve', 'paye', 'annule'])->default('en_attente');
            $table->text('description')->nullable(); // Description détaillée
            $table->string('beneficiaire')->nullable(); // Nom du bénéficiaire
            $table->string('reference_facture')->nullable(); // N° de facture ou référence
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'carte'])->nullable();
            $table->string('reference_paiement')->nullable(); // N° chèque, référence virement, etc.
            $table->foreignId('approuve_par')->nullable()->constrained('utilisateurs'); // Qui a approuvé
            $table->foreignId('paye_par')->nullable()->constrained('utilisateurs'); // Qui a payé
            $table->date('date_approbation')->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('observations')->nullable(); // Observations
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
