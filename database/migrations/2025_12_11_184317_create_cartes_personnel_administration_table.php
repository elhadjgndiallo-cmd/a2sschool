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
        if (!Schema::hasTable('cartes_personnel_administration')) {
            Schema::create('cartes_personnel_administration', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('personnel_administration_id');
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
                
                $table->foreign('personnel_administration_id', 'cpa_personnel_admin_id_fk')->references('id')->on('personnel_administration')->onDelete('cascade');
                $table->foreign('emise_par', 'cpa_emise_par_fk')->references('id')->on('utilisateurs')->onDelete('set null');
                $table->foreign('validee_par', 'cpa_validee_par_fk')->references('id')->on('utilisateurs')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartes_personnel_administration');
    }
};
