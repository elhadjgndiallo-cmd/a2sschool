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
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasTable('tranches_paiement')) {
                $table->foreignId('tranche_paiement_id')->nullable()->constrained('tranches_paiement')->onDelete('set null');
            } else {
                $table->unsignedBigInteger('tranche_paiement_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['tranche_paiement_id']);
            $table->dropColumn('tranche_paiement_id');
        });
    }
};
