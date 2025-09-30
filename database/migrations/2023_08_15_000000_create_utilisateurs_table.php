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
        // Vérifier si la table existe déjà
        if (!Schema::hasTable('utilisateurs')) {
            Schema::create('utilisateurs', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('prenom');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'enseignant', 'eleve', 'parent', 'personnel_admin'])->default('eleve');
                $table->string('telephone')->nullable();
                $table->string('adresse')->nullable();
                $table->date('date_naissance')->nullable();
                $table->enum('sexe', ['M', 'F'])->nullable();
                $table->string('photo_profil')->nullable();
                $table->boolean('actif')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // Si la table existe, vérifier si elle a la bonne structure
            $this->ensureTableStructure();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }

    /**
     * Vérifier et ajuster la structure de la table si nécessaire
     */
    private function ensureTableStructure(): void
    {
        $columns = Schema::getColumnListing('utilisateurs');
        
        // Vérifier les colonnes essentielles
        $requiredColumns = [
            'id', 'nom', 'prenom', 'email', 'password', 'role', 
            'telephone', 'adresse', 'date_naissance', 'sexe', 
            'photo_profil', 'actif', 'created_at', 'updated_at'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            // Ajouter les colonnes manquantes
            Schema::table('utilisateurs', function (Blueprint $table) use ($missingColumns) {
                foreach ($missingColumns as $column) {
                    switch ($column) {
                        case 'nom':
                            $table->string('nom')->nullable();
                            break;
                        case 'prenom':
                            $table->string('prenom')->nullable();
                            break;
                        case 'email':
                            $table->string('email')->unique()->nullable();
                            break;
                        case 'password':
                            $table->string('password')->nullable();
                            break;
                        case 'role':
                            $table->enum('role', ['admin', 'enseignant', 'eleve', 'parent', 'personnel_admin'])->default('eleve');
                            break;
                        case 'telephone':
                            $table->string('telephone')->nullable();
                            break;
                        case 'adresse':
                            $table->string('adresse')->nullable();
                            break;
                        case 'date_naissance':
                            $table->date('date_naissance')->nullable();
                            break;
                        case 'sexe':
                            $table->enum('sexe', ['M', 'F'])->nullable();
                            break;
                        case 'photo_profil':
                            $table->string('photo_profil')->nullable();
                            break;
                        case 'actif':
                            $table->boolean('actif')->default(true);
                            break;
                        case 'created_at':
                            $table->timestamp('created_at')->nullable();
                            break;
                        case 'updated_at':
                            $table->timestamp('updated_at')->nullable();
                            break;
                    }
                }
            });
        }
    }
};