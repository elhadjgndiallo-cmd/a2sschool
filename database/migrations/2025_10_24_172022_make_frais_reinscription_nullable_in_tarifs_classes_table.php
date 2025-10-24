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
        Schema::table('tarifs_classes', function (Blueprint $table) {
            $table->decimal('frais_reinscription', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifs_classes', function (Blueprint $table) {
            $table->decimal('frais_reinscription', 10, 2)->default(0)->change();
        });
    }
};
