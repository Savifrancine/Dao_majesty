<?php

namespace App\Support;

/**
 * Extrait les informations d'un marché saisi dans "Identification du marché"
 * (Formulaire EXP – 4.1) : lignes libellées "Nom du marché", "Marché",
 * "Autorité contractante", "Adresse"... Sans libellé, repli sur le format
 * "Marché n° {référence} - {nom}" posé par MarcheIdentification.
 */
class Exp41Marche
{
    /**
     * @return array{nom: string, reference: string, autorite_nom: string, autorite_adresse: string}
     */
    public static function parse(string $text): array
    {
        $result = ['nom' => '', 'reference' => '', 'autorite_nom' => '', 'autorite_adresse' => ''];

        foreach (preg_split('/\R/u', $text) as $line) {
            if (!preg_match('/^\s*([^:]+?)\s*:\s*(.*)$/u', $line, $matches)) {
                continue;
            }

            $label = self::normalize($matches[1]);
            $value = trim($matches[2]);
            if ($value === '') {
                continue;
            }

            if (str_starts_with($label, 'nom du marche')) {
                $result['nom'] = $value;
            } elseif ($label === 'marche' || str_starts_with($label, 'marche n')) {
                $result['reference'] = $value;
            } elseif (str_starts_with($label, 'autorite contractante')) {
                $result['autorite_nom'] = $value;
            } elseif ($label === 'adresse') {
                $result['autorite_adresse'] = $value;
            }
        }

        if ($result['nom'] === '' && $result['reference'] === '' && $result['autorite_nom'] === '') {
            $fallback = MarcheIdentification::parse($text);

            return ['nom' => $fallback['nom'], 'reference' => $fallback['reference'], 'autorite_nom' => '', 'autorite_adresse' => ''];
        }

        return $result;
    }

    private static function normalize(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');

        return strtr($lower, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);
    }
}
