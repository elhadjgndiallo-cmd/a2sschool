<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediteur_id')->constrained('utilisateurs');
            $table->foreignId('destinataire_id')->constrained('utilisateurs');
            $table->string('sujet');
            $table->text('contenu');
            $table->string('piece_jointe')->nullable();
            $table->boolean('lu')->default(false);
            $table->timestamp('date_lecture')->nullable();
            $table->boolean('supprime_expediteur')->default(false);
            $table->boolean('supprime_destinataire')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances des requêtes
            $table->index('expediteur_id');
            $table->index('destinataire_id');
            $table->index('lu');
            $table->index('supprime_expediteur');
            $table->index('supprime_destinataire');
            $table->index('created_at');
        });
    }

    /**
     * Annuler les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};