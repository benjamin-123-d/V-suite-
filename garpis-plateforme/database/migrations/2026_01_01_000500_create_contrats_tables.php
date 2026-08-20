<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contrat d'apprentissage — varie selon le metier, 1 page, Word + PDF.
        Schema::create('contrats_apprentissage', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('inscription_id')->unique()->constrained('inscriptions');
            $t->string('numero')->unique();
            $t->string('nom_representant')->nullable();
            $t->string('qualite_representant')->nullable();   // Parent | Tuteur | Lui-meme
            $t->string('duree_texte')->nullable();
            $t->text('echeancier_texte')->nullable();
            $t->string('fonction_signataire')->default('Présidente');  // varie : Directrice ou DE
            $t->boolean('clause_image_signee')->default(false);
            $t->string('scan_signe_url')->nullable();
            $t->timestamps();
        });

        // Contrats tiers — partenaires qui financent des cohortes.
        Schema::create('tiers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('raison_sociale');
            $t->string('type', 20)->nullable();               // ONG|ENTREPRISE|MAIRIE|PROJET
            $t->string('contact_nom')->nullable();
            $t->string('contact_tel')->nullable();
            $t->string('contact_email')->nullable();
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        Schema::create('contrats', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('tiers_id')->constrained('tiers');
            $t->string('numero')->unique();
            $t->string('objet');
            $t->unsignedInteger('montant_total');
            $t->date('date_debut')->nullable();
            $t->date('date_fin')->nullable();
            $t->string('statut', 16)->default('ACTIF');
            $t->timestamps();
        });

        Schema::create('echeances_contrat', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $t->unsignedTinyInteger('rang');
            $t->unsignedInteger('montant');
            $t->date('date_echeance');
        });

        Schema::create('reglements_tiers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $t->unsignedInteger('montant');
            $t->string('mode', 16);
            $t->date('recu_le');
            $t->foreignUuid('saisi_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglements_tiers');
        Schema::dropIfExists('echeances_contrat');
        Schema::dropIfExists('contrats');
        Schema::dropIfExists('tiers');
        Schema::dropIfExists('contrats_apprentissage');
    }
};
