<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annee_scolaires')->onDelete('cascade');
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();
            $table->decimal('sous_total', 12, 2);
            $table->enum('remise_type', ['pourcentage', 'montant'])->default('montant');
            $table->decimal('remise_valeur', 12, 2)->default(0);
            $table->decimal('montant_remise', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'carte', 'mobile_money']);
            $table->string('reference_paiement')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['payee', 'annulee'])->default('payee');
            $table->foreignId('genere_par')->constrained('utilisateurs')->onDelete('cascade');
            $table->timestamps();

            $table->index(['date_facture', 'eleve_id']);
        });

        Schema::create('facture_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->enum('type_frais', ['inscription', 'reinscription', 'scolarite', 'cantine', 'transport', 'activites', 'autre']);
            $table->date('mois');
            $table->string('libelle');
            $table->decimal('montant_brut', 12, 2);
            $table->decimal('montant_remise', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2);
            $table->foreignId('tranche_paiement_id')->nullable()->constrained('tranches_paiement')->nullOnDelete();
            $table->foreignId('frais_scolarite_id')->constrained('frais_scolarite')->onDelete('cascade');
            $table->foreignId('paiement_id')->nullable()->constrained('paiements')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_lignes');
        Schema::dropIfExists('factures');
    }
};
