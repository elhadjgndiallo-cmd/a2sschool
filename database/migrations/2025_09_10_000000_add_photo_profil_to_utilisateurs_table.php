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
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Ajouter la colonne photo_profil seulement si elle n'existe pas
            if (!Schema::hasColumn('utilisateurs', 'photo_profil')) {
                $table->string('photo_profil')->nullable()->after('sexe');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('photo_profil');
        });
    }
};