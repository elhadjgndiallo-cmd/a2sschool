<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $anneeScolaireActive = DB::table('annee_scolaires')->where('active', true)->first();

        if (!$anneeScolaireActive) {
            return;
        }

        $enseignantsSansAnnee = DB::table('enseignants')
            ->whereNull('annee_scolaire_id')
            ->orderBy('id')
            ->get();

        $dejaInscrits = DB::table('enseignants')
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->pluck('utilisateur_id')
            ->flip();

        foreach ($enseignantsSansAnnee as $enseignant) {
            if (isset($dejaInscrits[$enseignant->utilisateur_id])) {
                // Doublon créé lors de réinscriptions sans annee_scolaire_id : supprimer l'entrée orpheline
                DB::table('enseignant_matiere')->where('enseignant_id', $enseignant->id)->delete();
                DB::table('enseignants')->where('id', $enseignant->id)->delete();
                continue;
            }

            DB::table('enseignants')
                ->where('id', $enseignant->id)
                ->update(['annee_scolaire_id' => $anneeScolaireActive->id]);

            $dejaInscrits[$enseignant->utilisateur_id] = true;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non réversible sans risque de perte de données
    }
};
