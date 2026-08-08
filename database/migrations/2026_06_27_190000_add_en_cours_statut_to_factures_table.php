<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE factures MODIFY statut ENUM('payee', 'en_cours', 'annulee') NOT NULL DEFAULT 'payee'");

        DB::table('factures')
            ->whereRaw('total < ROUND(sous_total - montant_remise, 2) - 0.01')
            ->where('statut', 'payee')
            ->update(['statut' => 'en_cours']);
    }

    public function down(): void
    {
        DB::table('factures')->where('statut', 'en_cours')->update(['statut' => 'payee']);

        DB::statement("ALTER TABLE factures MODIFY statut ENUM('payee', 'annulee') NOT NULL DEFAULT 'payee'");
    }
};
