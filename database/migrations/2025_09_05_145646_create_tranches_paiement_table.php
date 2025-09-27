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
        Schema::create('tranches_paiement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frais_scolarite_id')->constrained('frais_scolarite')->onDelete('cascade');
            $table->integer('numero_tranche'); // 1, 2, 3, etc.
            $table->decimal('montant_tranche', 10, 2);
            $table->date('date_echeance');
            $table->enum('statut', ['en_attente', 'paye', 'en_retard', 'annule'])->default('en_attente');
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->date('date_paiement')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->unique(['frais_scolarite_id', 'numero_tranche']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tranches_paiement');
    }
};
