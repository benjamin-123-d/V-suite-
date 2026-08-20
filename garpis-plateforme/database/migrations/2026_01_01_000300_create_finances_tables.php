<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('inscription_id')->nullable()->constrained('inscriptions');
            $t->foreignUuid('commande_materiel_id')->nullable()->constrained('commandes_materiel');
            $t->uuid('demande_duplicata_id')->nullable();
            $t->foreignUuid('antenne_id')->constrained('antennes');
            $t->string('objet', 16)->default('FORMATION');  // FORMATION|MATERIEL|DUPLICATA|AUTRE
            $t->unsignedInteger('montant');                 // XOF entier, jamais de flottant
            $t->string('mode', 16);                         // ESPECES|MTN_MOMO|MOOV_MONEY|VIREMENT|CHEQUE|EN_LIGNE
            $t->string('reference_transaction')->nullable();
            $t->timestamp('paye_le')->useCurrent();
            $t->foreignUuid('encaisse_par')->nullable()->constrained('utilisateurs');
            $t->boolean('annule')->default(false);
            $t->timestamp('annule_le')->nullable();
            $t->foreignUuid('annule_par')->nullable()->constrained('utilisateurs');
            $t->string('annule_motif')->nullable();
            $t->timestamps();
            $t->index(['antenne_id', 'paye_le']);
        });

        Schema::create('recus', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('paiement_id')->unique()->constrained('paiements')->cascadeOnDelete();
            $t->string('numero')->unique();                  // GARPIS/2026/00042 — sequence UNIQUE organisation
            $t->unsignedSmallInteger('exercice');
            $t->string('reference_facture_normalisee')->nullable();  // prevu, non utilise
            $t->timestamp('emis_le')->useCurrent();
        });

        Schema::create('clotures_caisse', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('antenne_id')->constrained('antennes');
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $t->date('jour');
            $t->unsignedInteger('theorique_especes');
            $t->unsignedInteger('compte_especes');
            $t->integer('ecart');                            // peut etre negatif
            $t->string('justification')->nullable();
            $t->boolean('validee')->default(false);
            $t->foreignUuid('validee_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
            $t->unique(['antenne_id', 'utilisateur_id', 'jour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clotures_caisse');
        Schema::dropIfExists('recus');
        Schema::dropIfExists('paiements');
    }
};
