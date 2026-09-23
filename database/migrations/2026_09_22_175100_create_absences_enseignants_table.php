<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->enum('type', ['absence', 'retard', 'conge'])->default('absence');
            $table->enum('motif_categorie', ['maladie', 'personnel', 'mission', 'autre'])->nullable();
            $table->text('motif')->nullable();
            $table->string('document_justificatif')->nullable();
            $table->enum('origine', ['admin', 'enseignant'])->default('admin');
            $table->enum('statut', ['non_justifiee', 'en_attente', 'justifiee', 'refusee'])->default('non_justifiee');
            $table->foreignId('saisi_par')->constrained('utilisateurs');
            $table->foreignId('traite_par')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('traite_at')->nullable();
            $table->text('commentaire_admin')->nullable();
            $table->timestamps();

            $table->index(['enseignant_id', 'date_debut', 'date_fin']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences_enseignants');
    }
};
