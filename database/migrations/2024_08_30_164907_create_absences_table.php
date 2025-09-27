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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            $table->date('date_absence');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->enum('type', ['absence', 'retard', 'sortie_anticipee'])->default('absence');
            $table->enum('statut', ['non_justifiee', 'justifiee', 'en_attente'])->default('non_justifiee');
            $table->text('motif')->nullable();
            $table->string('document_justificatif')->nullable();
            $table->foreignId('saisi_par')->constrained('utilisateurs');
            $table->timestamp('notifie_parents_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
