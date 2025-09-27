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
        if (!Schema::hasTable('personnel_administration')) {
            Schema::create('personnel_administration', function (Blueprint $table) {
                $table->id();
                $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
                $table->string('poste'); // Secrétaire, Comptable, Surveillant, etc.
                $table->string('departement')->nullable(); // Administration, Comptabilité, etc.
                $table->date('date_embauche');
                $table->decimal('salaire', 10, 2)->nullable();
                $table->string('statut')->default('actif'); // actif, inactif, suspendu
                $table->json('permissions')->nullable(); // Permissions personnalisées
                $table->text('observations')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_administration');
    }
};