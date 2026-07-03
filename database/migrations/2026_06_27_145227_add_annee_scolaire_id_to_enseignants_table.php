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
        Schema::table('enseignants', function (Blueprint $table) {
            // Ajouter la colonne annee_scolaire_id
            $table->unsignedBigInteger('annee_scolaire_id')->nullable()->after('utilisateur_id');
            $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('set null');
            
            // Modifier la contrainte unique pour permettre le même numero_employe dans différentes années
            $table->dropUnique('enseignants_numero_employe_unique');
            $table->unique(['numero_employe', 'annee_scolaire_id'], 'enseignants_numero_employe_annee_unique');
        });
        
        // Mettre à jour les enseignants existants avec l'année scolaire active
        $anneeScolaireActive = DB::table('annee_scolaires')->where('active', true)->first();
        if ($anneeScolaireActive) {
            DB::table('enseignants')->update(['annee_scolaire_id' => $anneeScolaireActive->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            // Supprimer la contrainte unique composée
            $table->dropUnique('enseignants_numero_employe_annee_unique');
            
            // Recréer la contrainte unique simple
            $table->unique('numero_employe', 'enseignants_numero_employe_unique');
            
            // Supprimer la foreign key et la colonne
            $table->dropForeign(['annee_scolaire_id']);
            $table->dropColumn('annee_scolaire_id');
        });
    }
};
