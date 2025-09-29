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
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('description')->nullable();
            $table->string('lieu', 100)->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->boolean('journee_entiere')->default(false);
            $table->enum('type', ['cours', 'examen', 'reunion', 'conge', 'autre'])->default('autre');
            $table->string('couleur', 7)->nullable()->default('#3788d8');
            $table->boolean('public')->default(true);
            $table->unsignedBigInteger('classe_id')->nullable();
            $table->unsignedBigInteger('createur_id');
            $table->integer('rappel')->nullable()->comment('Rappel en minutes avant l\'événement');
            $table->timestamps();
            
            // Index pour améliorer les performances des requêtes fréquentes
            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('type');
            $table->index('public');
            $table->index('classe_id');
            $table->index('createur_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evenements');
    }
};