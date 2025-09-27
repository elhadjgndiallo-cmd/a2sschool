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
        Schema::table('messages', function (Blueprint $table) {
            // Ajouter les colonnes pour le système de notifications
            $table->string('expediteur_type')->default('parent')->after('expediteur_id');
            $table->string('destinataire_type')->default('admin')->after('destinataire_id');
            $table->string('titre')->nullable()->after('destinataire_type');
            $table->text('message')->nullable()->after('titre');
            $table->enum('type', ['question', 'demande', 'information', 'plainte', 'reponse', 'autre'])->default('question')->after('message');
            $table->enum('priorite', ['faible', 'moyenne', 'haute', 'urgente'])->default('moyenne')->after('type');
            $table->enum('statut', ['envoyee', 'lue', 'repondue'])->default('envoyee')->after('priorite');
            $table->boolean('lue')->default(false)->after('statut');
            $table->unsignedBigInteger('parent_id')->nullable()->after('lue');

            // Index pour les performances
            $table->index(['expediteur_type']);
            $table->index(['destinataire_type']);
            $table->index(['parent_id']);
            $table->index(['statut']);
            $table->index(['lue']);
            $table->index(['type']);
            $table->index(['priorite']);

            // Clé étrangère pour parent_id
            $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Supprimer les clés étrangères et index
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['expediteur_type']);
            $table->dropIndex(['destinataire_type']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['statut']);
            $table->dropIndex(['lue']);
            $table->dropIndex(['type']);
            $table->dropIndex(['priorite']);

            // Supprimer les colonnes
            $table->dropColumn([
                'expediteur_type',
                'destinataire_type',
                'titre',
                'message',
                'type',
                'priorite',
                'statut',
                'lue',
                'parent_id'
            ]);
        });
    }
};