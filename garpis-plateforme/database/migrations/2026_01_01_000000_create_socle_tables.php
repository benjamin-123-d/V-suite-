<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Tables techniques Laravel ---
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignUuid('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->mediumText('value');
            $t->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->string('owner');
            $t->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->string('queue')->index();
            $t->longText('payload');
            $t->unsignedTinyInteger('attempts');
            $t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at');
            $t->unsignedInteger('created_at');
        });

        Schema::create('failed_jobs', function (Blueprint $t) {
            $t->id();
            $t->string('uuid')->unique();
            $t->text('connection');
            $t->text('queue');
            $t->longText('payload');
            $t->longText('exception');
            $t->timestamp('failed_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        // --- Socle metier ---
        Schema::create('antennes', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('nom');
            $t->string('code_court', 8)->unique();   // COT, ABC
            $t->string('ville');
            $t->string('adresse')->nullable();
            $t->string('telephone')->nullable();
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        Schema::create('utilisateurs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('nom');
            $t->string('prenoms');
            $t->string('email')->unique();
            $t->string('telephone')->nullable();
            $t->string('password');
            $t->foreignUuid('antenne_id')->nullable()->constrained('antennes');
            $t->boolean('doit_changer_mdp')->default(true);
            $t->boolean('actif')->default(true);   // on desactive, on ne supprime jamais
            $t->timestamp('derniere_connexion')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code', 32)->unique();   // DIRECTRICE, DIR_ETUDES, SECRETAIRE, FORMATEUR, COMPTABLE
            $t->string('libelle');
        });

        // Les roles sont CUMULABLES : une personne peut etre DE et formateur.
        Schema::create('role_utilisateur', function (Blueprint $t) {
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs')->cascadeOnDelete();
            $t->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $t->primary(['utilisateur_id', 'role_id']);
        });

        // Sequences metier (numeros de recu, d'attestation) : une ligne par cle.
        Schema::create('compteurs', function (Blueprint $t) {
            $t->string('cle')->primary();       // RECU_2026, ATTESTATION_DEC_2026
            $t->unsignedInteger('valeur')->default(0);
        });

        Schema::create('journal_audit', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignUuid('utilisateur_id')->nullable()->constrained('utilisateurs');
            $t->string('action');               // PAIEMENT_CREE, ATTESTATION_REVOQUEE...
            $t->string('entite');
            $t->uuid('entite_id')->nullable();
            $t->json('avant')->nullable();
            $t->json('apres')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamp('survenu_le')->useCurrent();
            $t->index('survenu_le');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_audit');
        Schema::dropIfExists('compteurs');
        Schema::dropIfExists('role_utilisateur');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('utilisateurs');
        Schema::dropIfExists('antennes');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
    }
};
