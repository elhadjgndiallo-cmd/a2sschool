<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel_administration', function (Blueprint $table) {
            if (!Schema::hasColumn('personnel_administration', 'cycle')) {
                $table->string('cycle', 20)->nullable()->after('poste');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personnel_administration', function (Blueprint $table) {
            if (Schema::hasColumn('personnel_administration', 'cycle')) {
                $table->dropColumn('cycle');
            }
        });
    }
};
