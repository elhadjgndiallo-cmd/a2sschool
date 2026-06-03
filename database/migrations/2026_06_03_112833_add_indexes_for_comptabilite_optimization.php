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
        // Ajouter des index pour optimiser les requêtes de comptabilité
        
        // Index sur les dates d'entrées
        Schema::table('entrees', function (Blueprint $table) {
            $table->index('date_entree', 'idx_entrees_date');
            $table->index(['date_entree', 'source'], 'idx_entrees_date_source');
        });
        
        // Index sur les dates de paiements
        Schema::table('paiements', function (Blueprint $table) {
            $table->index('date_paiement', 'idx_paiements_date');
            $table->index(['date_paiement', 'frais_scolarite_id'], 'idx_paiements_date_frais');
        });
        
        // Index sur les dates de dépenses
        Schema::table('depenses', function (Blueprint $table) {
            $table->index('date_depense', 'idx_depenses_date');
            $table->index(['date_depense', 'type_depense'], 'idx_depenses_date_type');
        });
        
        // Index sur les salaires enseignants
        Schema::table('salaires_enseignants', function (Blueprint $table) {
            $table->index(['statut', 'date_paiement'], 'idx_salaires_statut_date');
        });
        
        // Index sur les élèves pour les jointures
        Schema::table('eleves', function (Blueprint $table) {
            $table->index('annee_scolaire_id', 'idx_eleves_annee');
        });
        
        // Index sur les frais de scolarité
        Schema::table('frais_scolarite', function (Blueprint $table) {
            $table->index(['eleve_id', 'type_frais'], 'idx_frais_eleve_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index en cas de rollback
        
        Schema::table('entrees', function (Blueprint $table) {
            $table->dropIndex('idx_entrees_date');
            $table->dropIndex('idx_entrees_date_source');
        });
        
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex('idx_paiements_date');
            $table->dropIndex('idx_paiements_date_frais');
        });
        
        Schema::table('depenses', function (Blueprint $table) {
            $table->dropIndex('idx_depenses_date');
            $table->dropIndex('idx_depenses_date_type');
        });
        
        Schema::table('salaires_enseignants', function (Blueprint $table) {
            $table->dropIndex('idx_salaires_statut_date');
        });
        
        Schema::table('eleves', function (Blueprint $table) {
            $table->dropIndex('idx_eleves_annee');
        });
        
        Schema::table('frais_scolarite', function (Blueprint $table) {
            $table->dropIndex('idx_frais_eleve_type');
        });
    }
};
