<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bons_salaire_enseignants')) {
            Schema::drop('bons_salaire_enseignants');
        }

        Schema::create('bons_salaire_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignants')->cascadeOnDelete();
            $table->foreignId('annee_scolaire_id')->nullable()->constrained('annee_scolaires')->nullOnDelete();
            $table->string('numero_bon', 50)->unique();
            $table->decimal('montant', 12, 2);
            $table->date('date_bon');
            $table->date('mois_reference')->nullable();
            $table->enum('statut', ['actif', 'deduit', 'annule'])->default('actif');
            $table->foreignId('salaire_enseignant_id')->nullable()->constrained('salaires_enseignants')->nullOnDelete();
            $table->date('deduit_le')->nullable();
            $table->string('mode_paiement', 30)->default('especes');
            $table->string('reference_paiement')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();

            $table->index(['enseignant_id', 'statut']);
            $table->index('mois_reference');
        });

        Schema::table('salaires_enseignants', function (Blueprint $table) {
            if (!Schema::hasColumn('salaires_enseignants', 'deduction_avances')) {
                $table->decimal('deduction_avances', 12, 2)->default(0)->after('deduction_autres');
            }
        });

        if (Schema::hasTable('depenses') && !Schema::hasColumn('depenses', 'bon_salaire_enseignant_id')) {
            Schema::table('depenses', function (Blueprint $table) {
                $table->unsignedBigInteger('bon_salaire_enseignant_id')->nullable();
            });
            Schema::table('depenses', function (Blueprint $table) {
                $table->foreign('bon_salaire_enseignant_id')
                    ->references('id')
                    ->on('bons_salaire_enseignants')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('depenses', 'bon_salaire_enseignant_id')) {
            Schema::table('depenses', function (Blueprint $table) {
                $table->dropForeign(['bon_salaire_enseignant_id']);
                $table->dropColumn('bon_salaire_enseignant_id');
            });
        }

        Schema::table('salaires_enseignants', function (Blueprint $table) {
            if (Schema::hasColumn('salaires_enseignants', 'deduction_avances')) {
                $table->dropColumn('deduction_avances');
            }
        });

        Schema::dropIfExists('bons_salaire_enseignants');
    }
};
