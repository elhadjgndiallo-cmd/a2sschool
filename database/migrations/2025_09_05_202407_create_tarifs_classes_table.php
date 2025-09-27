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
        Schema::create('tarifs_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->string('annee_scolaire'); // Ex: 2024-2025
            $table->decimal('frais_inscription', 10, 2)->default(0); // Frais d'inscription
            $table->decimal('frais_scolarite_mensuel', 10, 2)->default(0); // Frais de scolarité mensuel
            $table->decimal('frais_cantine_mensuel', 10, 2)->default(0); // Frais de cantine mensuel
            $table->decimal('frais_transport_mensuel', 10, 2)->default(0); // Frais de transport mensuel
            $table->decimal('frais_uniforme', 10, 2)->default(0); // Frais d'uniforme
            $table->decimal('frais_livres', 10, 2)->default(0); // Frais de livres
            $table->decimal('frais_autres', 10, 2)->default(0); // Autres frais
            $table->boolean('paiement_par_tranches')->default(true); // Paiement par tranches activé
            $table->integer('nombre_tranches')->default(12); // Nombre de tranches (12 mois)
            $table->enum('periode_tranche', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])->default('mensuel');
            $table->boolean('actif')->default(true); // Tarif actif ou non
            $table->text('description')->nullable(); // Description des frais
            $table->timestamps();
            
            $table->unique(['classe_id', 'annee_scolaire']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifs_classes');
    }
};
