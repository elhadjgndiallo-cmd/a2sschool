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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            $table->decimal('note', 5, 2); // Note sur 20
            $table->decimal('note_sur', 5, 2)->default(20); // Barème
            $table->enum('type_evaluation', ['devoir', 'controle', 'examen', 'oral', 'tp'])->default('devoir');
            $table->string('titre')->nullable();
            $table->text('commentaire')->nullable();
            $table->date('date_evaluation');
            $table->enum('periode', ['trimestre1', 'trimestre2', 'trimestre3', 'semestre1', 'semestre2'])->default('trimestre1');
            $table->integer('coefficient')->default(1);
            $table->boolean('rattrapage')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
