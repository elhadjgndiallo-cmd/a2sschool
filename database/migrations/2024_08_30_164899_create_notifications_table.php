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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('titre', 100);
            $table->string('message', 255);
            $table->enum('type', ['info', 'success', 'warning', 'danger'])->default('info');
            $table->string('lien', 255)->nullable();
            $table->string('icone', 50)->nullable();
            $table->boolean('lue')->default(false);
            $table->timestamps();
            
            // Index pour améliorer les performances des requêtes fréquentes
            $table->index('utilisateur_id');
            $table->index('lue');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};