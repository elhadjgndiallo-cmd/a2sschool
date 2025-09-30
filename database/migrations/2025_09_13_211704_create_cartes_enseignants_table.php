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
        if (!Schema::hasTable('cartes_enseignants')) {
            Schema::create('cartes_enseignants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enseignant_id');
                $table->string('numero_carte')->unique();
                $table->date('date_emission');
                $table->date('date_expiration');
                $table->enum('statut', ['active', 'expiree', 'suspendue', 'annulee'])->default('active');
                $table->enum('type_carte', ['standard', 'temporaire', 'remplacement'])->default('standard');
                $table->string('photo_path')->nullable();
                $table->text('qr_code')->nullable();
                $table->text('observations')->nullable();
                $table->unsignedBigInteger('emise_par')->nullable();
                $table->unsignedBigInteger('validee_par')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartes_enseignants');
    }
};
