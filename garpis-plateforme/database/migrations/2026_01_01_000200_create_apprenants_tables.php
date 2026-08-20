<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apprenants', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('nom');
            $t->string('prenoms');
            $t->char('sexe', 1)->nullable();          // F | M
            $t->date('date_naissance')->nullable();
            $t->string('photo_url')->nullable();
            $t->string('telephone')->index();
            $t->string('telephone_2')->nullable();
            $t->string('email')->nullable();
            $t->string('adresse')->nullable();
            $t->string('ville')->nullable();
            $t->string('niveau_etudes')->nullable();
            $t->string('profession')->nullable();
            $t->string('contact_urgence_nom')->nullable();
            $t->string('contact_urgence_tel')->nullable();
            $t->string('piece_type', 40)->nullable();
            $t->string('piece_numero')->nullable();
            $t->string('piece_scan_url')->nullable();
            $t->foreignUuid('antenne_origine_id')->nullable()->constrained('antennes');
            $t->timestamps();
        });

        Schema::create('inscriptions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('apprenant_id')->constrained('apprenants');
            $t->foreignUuid('session_id')->constrained('session_formation');
            $t->foreignUuid('antenne_id')->constrained('antennes');
            $t->date('date_inscription');
            $t->unsignedInteger('montant_du');            // XOF entier
            $t->unsignedInteger('remise_montant')->default(0);
            $t->string('remise_motif')->nullable();
            $t->foreignUuid('remise_par')->nullable()->constrained('utilisateurs');
            $t->boolean('remise_validee')->default(true); // false = au-dessus du plafond, attend la Directrice
            $t->string('statut', 16)->default('INSCRIT'); // INSCRIT|EN_COURS|TERMINE|ABANDON|ANNULE
            $t->foreignUuid('cree_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
            $t->unique(['apprenant_id', 'session_id']);
        });

        Schema::create('echeances', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $t->unsignedTinyInteger('rang');
            $t->unsignedInteger('montant');
            $t->date('date_echeance');
        });

        // GARPIS achete le materiel puis le facture : dette distincte, qui bloque
        // l'attestation au meme titre que le solde de formation.
        Schema::create('commandes_materiel', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $t->string('libelle');
            $t->unsignedInteger('montant_du');
            $t->date('commande_le');
            $t->foreignUuid('cree_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
        });

        Schema::create('seances', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('session_id')->constrained('session_formation')->cascadeOnDelete();
            $t->date('date_seance');
            $t->time('heure_debut')->nullable();
            $t->time('heure_fin')->nullable();
            $t->unique(['session_id', 'date_seance']);
        });

        Schema::create('presences_apprenant', function (Blueprint $t) {
            $t->foreignUuid('seance_id')->constrained('seances')->cascadeOnDelete();
            $t->foreignUuid('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $t->string('statut', 10);                 // PRESENT|ABSENT|RETARD|EXCUSE
            $t->foreignUuid('saisi_par')->nullable()->constrained('utilisateurs');
            $t->timestamp('saisi_le')->useCurrent();
            $t->primary(['seance_id', 'inscription_id']);
        });

        Schema::create('presences_formateur', function (Blueprint $t) {
            $t->foreignUuid('seance_id')->constrained('seances')->cascadeOnDelete();
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $t->decimal('heures', 4, 1)->nullable();  // des heures, pas de l'argent
            $t->primary(['seance_id', 'utilisateur_id']);
        });

        Schema::create('remunerations_formateur', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('utilisateur_id')->constrained('utilisateurs');
            $t->foreignUuid('session_id')->nullable()->constrained('session_formation');
            $t->string('periode', 7);                  // 2026-08
            $t->decimal('heures_reference', 6, 1)->nullable();  // indicatif : ne calcule PAS le montant
            $t->unsignedInteger('montant');            // saisi manuellement par le DE
            $t->date('verse_le')->nullable();
            $t->string('numero_decharge')->nullable()->unique();
            $t->foreignUuid('saisi_par')->nullable()->constrained('utilisateurs');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remunerations_formateur');
        Schema::dropIfExists('presences_formateur');
        Schema::dropIfExists('presences_apprenant');
        Schema::dropIfExists('seances');
        Schema::dropIfExists('commandes_materiel');
        Schema::dropIfExists('echeances');
        Schema::dropIfExists('inscriptions');
        Schema::dropIfExists('apprenants');
    }
};
