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
        // Migrer les notes existantes vers le nouveau format
        $notes = \App\Models\Note::whereNotNull('note')->get();
        
        foreach ($notes as $note) {
            // Copier la note existante vers note_cours
            $note->note_cours = $note->note;
            $note->note_finale = $note->note; // Note finale = note cours (pas de composition)
            $note->save();
        }
        
        // Supprimer l'ancien champ note après migration
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer l'ancien champ note
        Schema::table('notes', function (Blueprint $table) {
            $table->decimal('note', 5, 2)->nullable()->after('enseignant_id');
        });
        
        // Restaurer les données
        $notes = \App\Models\Note::whereNotNull('note_cours')->get();
        
        foreach ($notes as $note) {
            $note->note = $note->note_cours;
            $note->save();
        }
    }
};
