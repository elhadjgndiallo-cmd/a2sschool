<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->decimal('montant', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            $table->decimal('montant', 10, 2)->change();
        });
    }
};
