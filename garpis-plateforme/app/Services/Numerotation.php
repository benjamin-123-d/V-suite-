<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Sequences metier.
 *
 * Caisse commune => UNE seule serie de recus pour toute l'organisation,
 * continue, sans trou. Le verrou transactionnel est indispensable :
 * deux secretaires qui encaissent en meme temps a Cotonou et a Abomey-Calavi
 * ne doivent jamais obtenir le meme numero.
 */
class Numerotation
{
    /** GARPIS/2026/00042 */
    public static function prochainNumeroRecu(int $exercice): string
    {
        $valeur = self::incrementer('RECU_'.$exercice);

        return sprintf('GARPIS/%d/%05d', $exercice, $valeur);
    }

    /** GARPIS/DEC/2026/0157 */
    public static function prochainNumeroAttestation(string $codeFormation, int $exercice): string
    {
        $valeur = self::incrementer("ATTESTATION_{$codeFormation}_{$exercice}");

        return sprintf('GARPIS/%s/%d/%04d', $codeFormation, $exercice, $valeur);
    }

    /** GARPIS/CTR/2026/0031 */
    public static function prochainNumeroContrat(int $exercice): string
    {
        $valeur = self::incrementer('CONTRAT_'.$exercice);

        return sprintf('GARPIS/CTR/%d/%04d', $exercice, $valeur);
    }

    /** DEC-2026-08-0007 : decharge de remuneration formateur */
    public static function prochainNumeroDecharge(string $periode): string
    {
        $valeur = self::incrementer('DECHARGE_'.$periode);

        return sprintf('DEC-%s-%04d', $periode, $valeur);
    }

    /**
     * A appeler DANS une transaction ouverte par l'appelant.
     * lockForUpdate() serialise les acces concurrents sur la ligne du compteur.
     */
    private static function incrementer(string $cle): int
    {
        DB::table('compteurs')->insertOrIgnore(['cle' => $cle, 'valeur' => 0]);

        $ligne = DB::table('compteurs')->where('cle', $cle)->lockForUpdate()->first();
        $valeur = ((int) $ligne->valeur) + 1;

        DB::table('compteurs')->where('cle', $cle)->update(['valeur' => $valeur]);

        return $valeur;
    }

    /**
     * Code de verification d'attestation : aleatoire, JAMAIS derive du numero.
     * Sinon il suffirait d'incrementer pour deviner les attestations voisines.
     * Alphabet sans I, O, 0, 1 : lisible a l'oeil et dictable au telephone.
     */
    public static function codeVerification(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return substr($code, 0, 4).'-'.substr($code, 4);
    }
}
