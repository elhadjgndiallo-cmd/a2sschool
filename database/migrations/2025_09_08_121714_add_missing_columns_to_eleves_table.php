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
        Schema::table('eleves', function (Blueprint $table) {
            // Ajouter les colonnes manquantes pour le nouveau formulaire
            if (!Schema::hasColumn('eleves', 'type_inscription')) {
                $table->enum('type_inscription', ['nouvelle', 'reinscription', 'transfert'])->nullable()->after('date_inscription');
            }
            if (!Schema::hasColumn('eleves', 'ecole_origine')) {
                $table->string('ecole_origine')->nullable()->after('type_inscription');
            }
            if (!Schema::hasColumn('eleves', 'annee_scolaire_id')) {
                $table->unsignedBigInteger('annee_scolaire_id')->nullable()->after('classe_id');
            }
            if (!Schema::hasColumn('eleves', 'situation_matrimoniale')) {
                $table->enum('situation_matrimoniale', ['celibataire', 'marie', 'divorce', 'veuf'])->nullable()->after('ecole_origine');
            }
            if (!Schema::hasColumn('eleves', 'exempte_frais')) {
                $table->boolean('exempte_frais')->default(false)->after('situation_matrimoniale');
            }
            if (!Schema::hasColumn('eleves', 'paiement_annuel')) {
                $table->boolean('paiement_annuel')->default(false)->after('exempte_frais');
            }
            
            // Ajouter la clé étrangère pour annee_scolaire_id si elle n'existe pas
            if (Schema::hasColumn('eleves', 'annee_scolaire_id') && Schema::hasTable('annee_scolaires')) {
                $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            // Supprimer la clé étrangère d'abord
            if (Schema::hasColumn('eleves', 'annee_scolaire_id')) {
                $table->dropForeign(['annee_scolaire_id']);
            }
            
            // Supprimer les colonnes ajoutées
            $columns = ['type_inscription', 'ecole_origine', 'annee_scolaire_id', 'situation_matrimoniale', 'exempte_frais', 'paiement_annuel'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('eleves', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};