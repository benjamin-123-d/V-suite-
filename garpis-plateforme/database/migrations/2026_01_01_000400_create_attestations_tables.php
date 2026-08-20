<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('inscription_id')->unique()->constrained('inscriptions');
            $t->string('numero')->unique();                  // GARPIS/DEC/2026/0157
            $t->string('code_verification', 12)->unique();   // K7M2-P4XQ — aleatoire, JAMAIS derive du numero
            $t->json('donnees_figees');                      // contenu au moment de l'emission
            $t->timestamp('delivree_le')->useCurrent();
            $t->foreignUuid('delivree_par')->nullable()->constrained('utilisateurs');
            $t->boolean('revoquee')->default(false);
            $t->timestamp('revoquee_le')->nullable();
            $t->foreignUuid('revoquee_par')->nullable()->constrained('utilisateurs');
            $t->string('revocation_motif')->nullable();
            $t->timestamps();
        });

        Schema::create('scans_verification', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignUuid('attestation_id')->nullable()->constrained('attestations');
            $t->string('code_tente', 20)->nullable();        // trace aussi les codes inconnus
            $t->string('ip_anonymisee', 45)->nullable();     // 3 premiers octets seulement
            $t->string('user_agent')->nullable();
            $t->timestamp('scanne_le')->useCurrent();
        });

        Schema::create('demandes_duplicata', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('attestation_id')->constrained('attestations');
            $t->string('jeton', 64)->unique();               // aleatoire, usage unique
            $t->unsignedInteger('montant');
            $t->string('statut', 12)->default('EN_ATTENTE'); // EN_ATTENTE|PAYE|EXPIRE|ANNULE
            $t->foreignUuid('initiee_par')->nullable()->constrained('utilisateurs');
            $t->timestamp('initiee_le')->useCurrent();
            $t->timestamp('expire_le');
            $t->timestamp('paye_le')->nullable();
            $t->string('reference_paiement')->nullable();
            $t->timestamps();
        });

        Schema::create('conversations_ia', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $t->text('question');
            $t->text('reponse')->nullable();
            $t->json('outils_appeles')->nullable();
            $t->unsignedInteger('tokens_entree')->nullable();
            $t->unsignedInteger('tokens_sortie')->nullable();
            $t->unsignedInteger('cout_estime_xof')->default(0);
            $t->timestamp('survenu_le')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations_ia');
        Schema::dropIfExists('demandes_duplicata');
        Schema::dropIfExists('scans_verification');
        Schema::dropIfExists('attestations');
    }
};
