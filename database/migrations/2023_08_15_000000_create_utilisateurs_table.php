<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'enseignant', 'eleve', 'parent', 'personnel_administration'])->default('eleve');
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->date('date_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->string('photo_profil')->nullable();
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('email');
            $table->index('role');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('utilisateurs');
    }
};
