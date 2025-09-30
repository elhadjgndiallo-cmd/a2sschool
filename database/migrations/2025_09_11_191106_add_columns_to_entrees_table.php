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
        Schema::table('entrees', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes seulement si elles n'existent pas déjà
            if (!Schema::hasColumn('entrees', 'libelle')) {
                $table->string('libelle')->after('id');
            }
            if (!Schema::hasColumn('entrees', 'description')) {
                $table->text('description')->nullable()->after('libelle');
            }
            if (!Schema::hasColumn('entrees', 'montant')) {
                $table->decimal('montant', 10, 2)->after('description');
            }
            if (!Schema::hasColumn('entrees', 'date_entree')) {
                $table->date('date_entree')->after('montant');
            }
            if (!Schema::hasColumn('entrees', 'source')) {
                $table->string('source')->after('date_entree');
            }
            if (!Schema::hasColumn('entrees', 'mode_paiement')) {
                $table->string('mode_paiement')->default('especes')->after('source');
            }
            if (!Schema::hasColumn('entrees', 'reference')) {
                $table->string('reference')->nullable()->after('mode_paiement');
            }
            if (!Schema::hasColumn('entrees', 'enregistre_par')) {
                $table->foreignId('enregistre_par')->constrained('utilisateurs')->after('reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrees', function (Blueprint $table) {
            // Vérifier l'existence des colonnes avant de les supprimer
            if (Schema::hasColumn('entrees', 'enregistre_par')) {
                $table->dropForeign(['enregistre_par']);
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('entrees', 'libelle')) $columnsToDrop[] = 'libelle';
            if (Schema::hasColumn('entrees', 'description')) $columnsToDrop[] = 'description';
            if (Schema::hasColumn('entrees', 'montant')) $columnsToDrop[] = 'montant';
            if (Schema::hasColumn('entrees', 'date_entree')) $columnsToDrop[] = 'date_entree';
            if (Schema::hasColumn('entrees', 'source')) $columnsToDrop[] = 'source';
            if (Schema::hasColumn('entrees', 'mode_paiement')) $columnsToDrop[] = 'mode_paiement';
            if (Schema::hasColumn('entrees', 'reference')) $columnsToDrop[] = 'reference';
            if (Schema::hasColumn('entrees', 'enregistre_par')) $columnsToDrop[] = 'enregistre_par';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
