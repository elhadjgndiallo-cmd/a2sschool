<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('super_admin', 'admin', 'teacher', 'student', 'parent', 'personnel_admin') NOT NULL DEFAULT 'student'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent', 'personnel_admin') NOT NULL DEFAULT 'student'");
        }
    }
};
