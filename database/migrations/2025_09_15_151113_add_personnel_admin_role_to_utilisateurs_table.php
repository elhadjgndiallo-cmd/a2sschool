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
        // Pour MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent', 'personnel_admin') NOT NULL DEFAULT 'student'");
        }
        // Pour SQLite, on ne peut pas modifier les contraintes, mais on peut ajouter une colonne temporaire
        elseif (DB::getDriverName() === 'sqlite') {
            // SQLite ne supporte pas la modification d'ENUM, donc on fait une migration de données
            // Cette migration est principalement pour MySQL, SQLite utilisera la migration principale
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pour MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL DEFAULT 'student'");
        }
    }
};