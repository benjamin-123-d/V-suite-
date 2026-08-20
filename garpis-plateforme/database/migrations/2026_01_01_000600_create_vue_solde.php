<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Source unique de verite du solde.
 * Le blocage des attestations s'appuie sur cette vue, jamais sur un calcul
 * refait a la main dans le code metier.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW v_solde_inscription AS
            SELECT
                i.id AS inscription_id,
                (i.montant_du - i.remise_montant)            AS du_formation,
                COALESCE(cm.total_materiel, 0)               AS du_materiel,
                COALESCE(p.total_verse, 0)                   AS total_verse,
                (i.montant_du - i.remise_montant)
                    + COALESCE(cm.total_materiel, 0)
                    - COALESCE(p.total_verse, 0)             AS solde
            FROM inscriptions i
            LEFT JOIN (
                SELECT inscription_id, SUM(montant_du) AS total_materiel
                FROM commandes_materiel GROUP BY inscription_id
            ) cm ON cm.inscription_id = i.id
            LEFT JOIN (
                SELECT inscription_id, SUM(montant) AS total_verse
                FROM paiements
                WHERE annule = false AND objet IN ('FORMATION','MATERIEL')
                GROUP BY inscription_id
            ) p ON p.inscription_id = i.id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_solde_inscription');
    }
};
