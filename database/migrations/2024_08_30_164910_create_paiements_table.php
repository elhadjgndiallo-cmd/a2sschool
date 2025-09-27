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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frais_scolarite_id')->constrained('frais_scolarite')->onDelete('cascade');
            $table->decimal('montant_paye', 10, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'carte', 'mobile_money'])->default('especes');
            $table->string('reference_paiement')->nullable(); // Numéro de chèque, référence virement, etc.
            $table->string('numero_recu')->unique();
            $table->text('observations')->nullable();
            $table->foreignId('encaisse_par')->constrained('utilisateurs'); // Qui a encaissé
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
