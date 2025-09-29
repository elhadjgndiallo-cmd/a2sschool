<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table Document temporairement désactivée - pas besoin de gérer les documents pour le moment
        /*
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('description')->nullable();
            $table->string('type', 50); // cours, devoir, examen, administratif, autre
            $table->string('categorie', 50)->nullable();
            $table->string('chemin');
            $table->unsignedBigInteger('taille'); // taille en octets
            $table->string('format', 10); // extension du fichier
            $table->boolean('public')->default(true);
            $table->unsignedInteger('telechargements')->default(0);
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('createur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances des requêtes
            $table->index('type');
            $table->index('categorie');
            $table->index('public');
            $table->index('created_at');
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Table Document temporairement désactivée
        // Schema::dropIfExists('documents');
    }
};