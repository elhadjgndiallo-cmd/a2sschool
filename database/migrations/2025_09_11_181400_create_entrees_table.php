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
        Schema::create('entrees', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->decimal('montant', 10, 2);
            $table->date('date_entree');
            $table->string('source'); // Paiements scolaires, Dons, Subventions, etc.
            $table->string('mode_paiement')->default('especes'); // especes, virement, cheque
            $table->string('reference')->nullable(); // Numéro de chèque, référence virement
            $table->foreignId('enregistre_par')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrees');
    }
};
