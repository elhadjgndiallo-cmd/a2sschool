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
            // Vérifier si les colonnes existent avant de les ajouter
            if (!Schema::hasColumn('utilisateurs', 'nom')) {
                $table->string('nom')->after('id');
            }
            if (!Schema::hasColumn('utilisateurs', 'prenom')) {
                $table->string('prenom')->after('nom');
            }
            if (!Schema::hasColumn('utilisateurs', 'telephone')) {
                $table->string('telephone', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('utilisateurs', 'adresse')) {
                $table->text('adresse')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('utilisateurs', 'sexe')) {
                $table->enum('sexe', ['M', 'F'])->nullable()->after('adresse');
            }
            if (!Schema::hasColumn('utilisateurs', 'date_naissance')) {
                $table->date('date_naissance')->nullable()->after('sexe');
            }
            if (!Schema::hasColumn('utilisateurs', 'lieu_naissance')) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance');
            }
            if (!Schema::hasColumn('utilisateurs', 'photo_profil')) {
                $table->string('photo_profil')->nullable()->after('lieu_naissance');
            }
            if (!Schema::hasColumn('utilisateurs', 'actif')) {
                $table->boolean('actif')->default(true)->after('photo_profil');
            }
            
            // Rendre le champ name nullable s'il existe ou le supprimer
            if (Schema::hasColumn('utilisateurs', 'name')) {
                $table->string('name')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'nom', 'prenom', 'telephone', 'adresse', 'sexe', 
                'date_naissance', 'lieu_naissance', 'photo_profil', 'actif'
            ]);
            
            // Remettre name comme requis
            if (Schema::hasColumn('utilisateurs', 'name')) {
                $table->string('name')->nullable(false)->change();
            }
        });
    }
};
