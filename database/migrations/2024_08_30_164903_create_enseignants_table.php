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
        Schema::create('enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('numero_employe')->unique();
            $table->date('date_embauche');
            $table->string('specialite')->nullable(); // Spécialité principale
            $table->enum('statut', ['titulaire', 'contractuel', 'vacataire'])->default('contractuel');
            $table->decimal('salaire', 10, 2)->nullable();
            $table->text('qualifications')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};
