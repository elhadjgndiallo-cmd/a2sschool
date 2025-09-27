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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Ex: 6ème A, Terminale S1
            $table->string('niveau'); // Ex: 6ème, 5ème, Terminale
            $table->string('section')->nullable(); // Ex: A, B, S1, L1
            $table->integer('effectif_max')->default(30);
            $table->integer('effectif_actuel')->default(0);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
