<?php

namespace App\Services;

/**
 * Le franc CFA n'a pas de centimes : tout est en entiers.
 * Un flottant sur de l'argent finit toujours par produire un ecart de caisse
 * que personne ne sait expliquer.
 */
class Montant
{
    public static function format(int $montant): string
    {
        return number_format($montant, 0, ',', ' ');
    }

    public static function formatFcfa(int $montant): string
    {
        return self::format($montant).' FCFA';
    }

    /** Conversion en toutes lettres, pour les recus et les contrats. */
    public static function enLettres(int $montant): string
    {
        if ($montant === 0) {
            return 'zéro franc CFA';
        }

        $texte = self::convertir($montant);

        return trim($texte).' francs CFA';
    }

    private static function convertir(int $n): string
    {
        $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
            'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
            'dix-sept', 'dix-huit', 'dix-neuf'];
        $dizaines = [2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante',
            6 => 'soixante', 7 => 'soixante', 8 => 'quatre-vingt', 9 => 'quatre-vingt'];

        if ($n < 20) {
            return $unites[$n];
        }

        if ($n < 100) {
            $d = intdiv($n, 10);
            $u = $n % 10;

            if ($d === 7 || $d === 9) {
                $u += 10;
                $d--;
            }

            $texte = $dizaines[$d];

            // quatre-vingts prend un s, sauf suivi d'un autre nombre
            if ($d === 8 && $u === 0) {
                $texte .= 's';
            }

            if ($u === 1 && $d !== 8) {
                $texte .= ' et un';
            } elseif ($u === 11 && $d === 7) {
                $texte .= ' et onze';
            } elseif ($u > 0) {
                $texte .= '-'.$unites[$u];
            }

            return $texte;
        }

        if ($n < 1000) {
            $c = intdiv($n, 100);
            $reste = $n % 100;
            $texte = $c > 1 ? $unites[$c].' cent' : 'cent';

            // deux cents, mais deux cent cinquante
            if ($c > 1 && $reste === 0) {
                $texte .= 's';
            }

            return $reste ? $texte.' '.self::convertir($reste) : $texte;
        }

        if ($n < 1000000) {
            $m = intdiv($n, 1000);
            $reste = $n % 1000;
            $texte = $m > 1 ? self::convertir($m).' mille' : 'mille';

            return $reste ? $texte.' '.self::convertir($reste) : $texte;
        }

        $mi = intdiv($n, 1000000);
        $reste = $n % 1000000;
        $texte = self::convertir($mi).' million'.($mi > 1 ? 's' : '');

        return $reste ? $texte.' '.self::convertir($reste) : $texte;
    }
}
