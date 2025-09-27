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
        Schema::create('frais_scolarite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->string('libelle'); // Ex: Frais de scolarité T1, Frais d'inscription
            $table->decimal('montant', 10, 2);
            $table->date('date_echeance');
            $table->enum('statut', ['en_attente', 'paye', 'en_retard', 'annule'])->default('en_attente');
            $table->enum('type_frais', ['inscription', 'scolarite', 'cantine', 'transport', 'activites', 'autre'])->default('scolarite');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_scolarite');
    }
};
