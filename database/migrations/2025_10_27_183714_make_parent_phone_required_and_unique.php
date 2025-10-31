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
        // NE PAS poser de contrainte unique en base pour l'instant
        // afin de ne pas bloquer la migration à cause des doublons existants.
        // On garde l'unicité au niveau applicatif (validation Laravel) et
        // on pose uniquement un index non-unique pour la performance.
        Schema::table('utilisateurs', function (Blueprint $table) {
            if (!Schema::hasColumn('utilisateurs', 'telephone')) {
                return; // sécurité
            }
            // Ajouter un index simple si absent
            $table->index('telephone', 'idx_utilisateurs_telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Supprimer l'index simple si présent
            try {
                $table->dropIndex('idx_utilisateurs_telephone');
            } catch (\Throwable $e) {
                // ignorer si l'index n'existe pas
            }
        });
    }
};