<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index critiques pour améliorer les performances des requêtes les plus fréquentes
        
        // Table eleves - Requêtes par année scolaire et classe
        DB::statement('CREATE INDEX IF NOT EXISTS idx_eleves_annee_scolaire ON eleves(annee_scolaire_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_eleves_classe ON eleves(classe_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_eleves_actif ON eleves(actif)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_eleves_annee_classe ON eleves(annee_scolaire_id, classe_id)');
        
        // Table notes - Requêtes par élève, matière et période
        DB::statement('CREATE INDEX IF NOT EXISTS idx_notes_eleve ON notes(eleve_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_notes_matiere ON notes(matiere_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_notes_periode ON notes(periode)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_notes_date ON notes(date_evaluation)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_notes_eleve_periode ON notes(eleve_id, periode)');
        
        // Table absences - Requêtes par élève et date
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absences_eleve ON absences(eleve_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absences_date ON absences(date)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absences_eleve_date ON absences(eleve_id, date)');
        
        // Table enseignants - Requêtes par année scolaire
        DB::statement('CREATE INDEX IF NOT EXISTS idx_enseignants_annee ON enseignants(annee_scolaire_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_enseignants_actif ON enseignants(actif)');
        
        // Table emplois_temps - Requêtes par classe et enseignant
        DB::statement('CREATE INDEX IF NOT EXISTS idx_emplois_classe ON emplois_temps(classe_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_emplois_enseignant ON emplois_temps(enseignant_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_emplois_jour ON emplois_temps(jour_semaine)');
        
        // Table entrees - Requêtes par date (comptabilité)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_entrees_date ON entrees(date_entree)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_entrees_source ON entrees(source)');
        
        // Table depenses - Requêtes par date et type (comptabilité)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_depenses_date ON depenses(date_depense)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_depenses_type ON depenses(type_depense)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_depenses_statut ON depenses(statut)');
        
        // Table annee_scolaires - Requête de l'année active
        DB::statement('CREATE INDEX IF NOT EXISTS idx_annee_active ON annee_scolaires(active)');
        
        // Table parent_eleve - Table pivot
        DB::statement('CREATE INDEX IF NOT EXISTS idx_parent_eleve_parent ON parent_eleve(parent_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_parent_eleve_eleve ON parent_eleve(eleve_id)');
        
        // Table sessions - Performance de connexion
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_user ON sessions(user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_activity ON sessions(last_activity)');
        
        // Table utilisateurs - Recherches fréquentes
        DB::statement('CREATE INDEX IF NOT EXISTS idx_utilisateurs_role ON utilisateurs(role)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_utilisateurs_actif ON utilisateurs(actif)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index (IF EXISTS pour éviter les erreurs)
        DB::statement('DROP INDEX IF EXISTS idx_eleves_annee_scolaire ON eleves');
        DB::statement('DROP INDEX IF EXISTS idx_eleves_classe ON eleves');
        DB::statement('DROP INDEX IF EXISTS idx_eleves_actif ON eleves');
        DB::statement('DROP INDEX IF EXISTS idx_eleves_annee_classe ON eleves');
        
        DB::statement('DROP INDEX IF EXISTS idx_notes_eleve ON notes');
        DB::statement('DROP INDEX IF EXISTS idx_notes_matiere ON notes');
        DB::statement('DROP INDEX IF EXISTS idx_notes_periode ON notes');
        DB::statement('DROP INDEX IF EXISTS idx_notes_date ON notes');
        DB::statement('DROP INDEX IF EXISTS idx_notes_eleve_periode ON notes');
        
        DB::statement('DROP INDEX IF EXISTS idx_absences_eleve ON absences');
        DB::statement('DROP INDEX IF EXISTS idx_absences_date ON absences');
        DB::statement('DROP INDEX IF EXISTS idx_absences_eleve_date ON absences');
        
        DB::statement('DROP INDEX IF EXISTS idx_enseignants_annee ON enseignants');
        DB::statement('DROP INDEX IF EXISTS idx_enseignants_actif ON enseignants');
        
        DB::statement('DROP INDEX IF EXISTS idx_emplois_classe ON emplois_temps');
        DB::statement('DROP INDEX IF EXISTS idx_emplois_enseignant ON emplois_temps');
        DB::statement('DROP INDEX IF EXISTS idx_emplois_jour_semaine ON emplois_temps');
        
        DB::statement('DROP INDEX IF EXISTS idx_entrees_date ON entrees');
        DB::statement('DROP INDEX IF EXISTS idx_entrees_source ON entrees');
        
        DB::statement('DROP INDEX IF EXISTS idx_depenses_date ON depenses');
        DB::statement('DROP INDEX IF EXISTS idx_depenses_type ON depenses');
        DB::statement('DROP INDEX IF EXISTS idx_depenses_statut ON depenses');
        
        DB::statement('DROP INDEX IF EXISTS idx_annee_active ON annee_scolaires');
        
        DB::statement('DROP INDEX IF EXISTS idx_parent_eleve_parent ON parent_eleve');
        DB::statement('DROP INDEX IF EXISTS idx_parent_eleve_eleve ON parent_eleve');
        
        DB::statement('DROP INDEX IF EXISTS idx_sessions_user ON sessions');
        DB::statement('DROP INDEX IF EXISTS idx_sessions_activity ON sessions');
        
        DB::statement('DROP INDEX IF EXISTS idx_utilisateurs_role ON utilisateurs');
        DB::statement('DROP INDEX IF EXISTS idx_utilisateurs_actif ON utilisateurs');
    }
};
