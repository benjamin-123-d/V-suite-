<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code', 16)->unique();          // DEC, MKP, COU
            $t->string('intitule');
            $t->string('domaine')->nullable();
            $t->text('description')->nullable();
            $t->unsignedInteger('duree_heures')->nullable();
            $t->unsignedInteger('cout_reference')->nullable();   // XOF entier
            $t->text('descriptif_attestation')->nullable();      // paragraphe injecte dans l'attestation
            $t->boolean('actif')->default(true);
            $t->timestamps();
        });

        Schema::create('chapitres', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('formation_id')->constrained('formations')->cascadeOnDelete();
            $t->unsignedSmallInteger('ordre');
            $t->string('intitule');
            $t->text('objectifs')->nullable();
        });

        Schema::create('session_formation', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('formation_id')->constrained('formations');
            $t->foreignUuid('antenne_id')->constrained('antennes');
            $t->string('code')->unique();              // DEC-COT-2026-01
            $t->string('libelle');                     // "Session de Juillet 2026"
            $t->string('type', 20)->default('BASE');   // BASE | PROFESSIONNELLE
            $t->date('date_debut');
            $t->date('date_fin');
            $t->string('jours_horaires')->nullable();
            $t->string('lieu')->nullable();
            $t->unsignedInteger('cout');
            $t->unsignedSmallInteger('capacite_max')->nullable();
            $t->string('statut', 20)->default('PLANIFIEE');  // PLANIFIEE|EN_COURS|TERMINEE|ARCHIVEE
            $t->foreignUuid('cree_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
        });

        Schema::create('session_formateur', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('session_id')->constrained('session_formation')->cascadeOnDelete();
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $t->string('mode_remuneration', 12)->default('HORAIRE');  // HORAIRE | FORFAIT
            $t->unsignedInteger('taux_horaire')->nullable();
            $t->unsignedInteger('montant_forfait')->nullable();
        });

        Schema::create('session_chapitre', function (Blueprint $t) {
            $t->foreignUuid('session_id')->constrained('session_formation')->cascadeOnDelete();
            $t->foreignUuid('chapitre_id')->constrained('chapitres')->cascadeOnDelete();
            $t->string('statut', 12)->default('A_VENIR');   // A_VENIR | EN_COURS | TERMINE
            $t->timestamp('maj_le')->nullable();
            $t->foreignUuid('maj_par')->nullable()->constrained('utilisateurs');
            $t->primary(['session_id', 'chapitre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_chapitre');
        Schema::dropIfExists('session_formateur');
        Schema::dropIfExists('session_formation');
        Schema::dropIfExists('chapitres');
        Schema::dropIfExists('formations');
    }
};
