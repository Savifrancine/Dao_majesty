<?php

namespace App\Support;

/**
 * Convertit un nombre en toutes lettres françaises (utilisé pour épeler les
 * montants FCFA dans les documents générés : bordereaux, lettre de soumission...).
 */
class NumberToWords
{
    public static function french(float|int|string|null $value): string
    {
        $value = (int) round((float) $value);
        if ($value === 0) {
            return 'zéro';
        }

        if (extension_loaded('intl')) {
            $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $formatted = $formatter->format($value);
            if ($formatted !== false && !preg_match('/\d/', $formatted)) {
                return trim(mb_strtolower($formatted));
            }
        }

        return self::spellOut($value);
    }

    private static function spellOut(int $number): string
    {
        $units = [
            0 => 'zéro', 1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
            6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix',
            11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze',
            16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf',
        ];
        $tens = [
            0 => '', 1 => 'dix', 2 => 'vingt', 3 => 'trente', 4 => 'quarante',
            5 => 'cinquante', 6 => 'soixante', 7 => 'soixante-dix', 8 => 'quatre-vingt',
            9 => 'quatre-vingt-dix',
        ];

        if ($number < 20) {
            return $units[$number];
        }
        if ($number < 100) {
            $d = (int) floor($number / 10);
            $u = $number % 10;
            if ($d === 7 || $d === 9) {
                // soixante-dix..soixante-dix-neuf / quatre-vingt-dix..quatre-vingt-dix-neuf :
                // "soixante et onze" (71) prend "et", tout le reste un tiret.
                $base = $tens[$d - 1];
                $unitNumber = 10 + $u;
                $separator = ($d === 7 && $u === 1) ? ' et ' : '-';
                return $base . $separator . $units[$unitNumber];
            }
            $word = $tens[$d];
            if ($u === 0) {
                return $word;
            }
            // "et" seulement pour 21/31/41/51/61 (vingt et un... soixante et un) ;
            // quatre-vingt-un (81) garde le tiret, jamais de "et".
            $separator = ($u === 1 && $d >= 2 && $d <= 6) ? ' et ' : '-';
            return $word . $separator . $units[$u];
        }
        if ($number < 1000) {
            $hundreds = (int) floor($number / 100);
            $remainder = $number % 100;
            $hundredsText = $hundreds === 1 ? 'cent' : $units[$hundreds] . ' cent';
            if ($remainder === 0) {
                return $hundredsText;
            }
            return $hundredsText . ' ' . self::spellOut($remainder);
        }
        if ($number < 1000000) {
            $thousands = (int) floor($number / 1000);
            $remainder = $number % 1000;
            $prefix = $thousands === 1 ? 'mille' : self::spellOut($thousands) . ' mille';
            if ($remainder === 0) {
                return $prefix;
            }
            return $prefix . ' ' . self::spellOut($remainder);
        }
        $millions = (int) floor($number / 1000000);
        $remainder = $number % 1000000;
        $prefix = $millions === 1 ? 'un million' : self::spellOut($millions) . ' millions';
        if ($remainder === 0) {
            return $prefix;
        }
        return $prefix . ' ' . self::spellOut($remainder);
    }
}
