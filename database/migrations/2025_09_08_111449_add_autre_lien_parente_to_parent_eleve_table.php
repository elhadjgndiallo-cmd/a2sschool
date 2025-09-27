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
        Schema::table('parent_eleve', function (Blueprint $table) {
            // Ajouter la colonne lien_parente si elle n'existe pas
            if (!Schema::hasColumn('parent_eleve', 'lien_parente')) {
                $table->string('lien_parente')->default('parent')->after('contact_urgence');
            }
            
            // Ajouter la colonne autre_lien_parente si elle n'existe pas
            if (!Schema::hasColumn('parent_eleve', 'autre_lien_parente')) {
                $table->string('autre_lien_parente')->nullable()->after('lien_parente');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_eleve', function (Blueprint $table) {
            $table->dropColumn(['autre_lien_parente', 'autorise_sortie']);
        });
    }
};
