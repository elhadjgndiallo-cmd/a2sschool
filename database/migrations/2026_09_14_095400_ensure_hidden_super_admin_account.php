<?php

use App\Models\Utilisateur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('utilisateurs')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('super_admin', 'admin', 'teacher', 'student', 'parent', 'personnel_admin') NOT NULL DEFAULT 'student'");
        }

        Utilisateur::ensureHiddenSuperAdmin();
    }

    public function down(): void
    {
        // Le compte système n'est pas supprimé volontairement.
    }
};
