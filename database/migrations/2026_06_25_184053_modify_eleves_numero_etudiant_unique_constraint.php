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
            // Supprimer la contrainte unique sur numero_etudiant seul
            $table->dropUnique('eleves_numero_etudiant_unique');
            
            // Créer une contrainte unique composée (numero_etudiant + annee_scolaire_id)
            // Cela permet au même élève d'avoir le même matricule dans différentes années
            $table->unique(['numero_etudiant', 'annee_scolaire_id'], 'eleves_numero_etudiant_annee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eleves', function (Blueprint $table) {
            // Supprimer la contrainte composée
            $table->dropUnique('eleves_numero_etudiant_annee_unique');
            
            // Recréer la contrainte unique simple
            $table->unique('numero_etudiant', 'eleves_numero_etudiant_unique');
        });
    }
};
