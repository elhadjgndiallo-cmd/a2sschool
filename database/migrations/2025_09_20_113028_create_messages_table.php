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
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('expediteur_id');
                $table->string('expediteur_type'); // 'parent', 'admin', 'personnel_admin'
                $table->unsignedBigInteger('destinataire_id');
                $table->string('destinataire_type'); // 'parent', 'admin', 'personnel_admin'
                $table->string('titre');
                $table->text('message');
                $table->enum('type', ['question', 'demande', 'information', 'plainte', 'reponse', 'autre'])->default('question');
                $table->enum('priorite', ['faible', 'moyenne', 'haute', 'urgente'])->default('moyenne');
                $table->enum('statut', ['envoyee', 'lue', 'repondue'])->default('envoyee');
                $table->boolean('lue')->default(false);
                $table->unsignedBigInteger('parent_id')->nullable(); // Pour les réponses
                $table->timestamps();

                // Index pour les performances
                $table->index(['destinataire_id', 'destinataire_type']);
                $table->index(['expediteur_id', 'expediteur_type']);
                $table->index(['parent_id']);
                $table->index(['statut']);
                $table->index(['lue']);
                $table->index(['type']);
                $table->index(['priorite']);

                // Clés étrangères
                if (Schema::hasTable('utilisateurs')) {
                    $table->foreign('expediteur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                    $table->foreign('destinataire_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                }
                $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};