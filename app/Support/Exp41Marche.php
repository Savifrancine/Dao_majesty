<?php

namespace App\Support;

/**
 * Extrait les informations d'un marché saisi dans "Identification du marché"
 * (Formulaire EXP – 4.1) : lignes libellées "Nom du marché", "Marché",
 * "Autorité contractante", "Adresse"... Les segments peuvent être séparés par
 * de vrais retours à la ligne (saisie directe) ou par " - " (texte reformaté
 * par le report depuis le formulaire de qualification, au format
 * "Marché n° {référence} - {reste}" posé par MarcheIdentification::format).
 * Sans aucun libellé reconnu, le texte entier est pris comme nom du marché.
 */
class Exp41Marche
{
    private const LABELS = 'Nom du march[ée]|March[ée]|Autorit[ée] contractante|Adresse';

    /**
     * @return array{nom: string, reference: string, autorite_nom: string, autorite_adresse: string}
     */
    public static function parse(string $text): array
    {
        $result = ['nom' => '', 'reference' => '', 'autorite_nom' => '', 'autorite_adresse' => ''];
        $text = trim($text);

        // Le report depuis la qualification préfixe tout le texte par
        // "Marché n° {référence}", suivi d'un " - " ou d'un retour à la ligne.
        if (preg_match('/^March[ée]\s*n°\s*(.*?)(?:\s+-\s+|\R|$)(.*)$/isu', $text, $prefixMatch)) {
            $result['reference'] = trim($prefixMatch[1]);
            $text = trim($prefixMatch[2]);
        }

        // Normalise les segments restants séparés par " - Libellé :" en vrais
        // retours à la ligne, pour les traiter comme une saisie multi-lignes.
        $normalized = preg_replace('/\s*-\s*(?=(?:' . self::LABELS . ')\s*:)/iu', "\n", $text);

        $foundLabel = false;
        foreach (preg_split('/\R/u', $normalized) as $line) {
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
                $foundLabel = true;
            } elseif ($label === 'marche' || str_starts_with($label, 'marche n')) {
                if ($result['reference'] === '') {
                    $result['reference'] = $value;
                }
                $foundLabel = true;
            } elseif (str_starts_with($label, 'autorite contractante')) {
                $result['autorite_nom'] = $value;
                $foundLabel = true;
            } elseif ($label === 'adresse') {
                $result['autorite_adresse'] = $value;
                $foundLabel = true;
            }
        }

        if (!$foundLabel && $result['nom'] === '') {
            $result['nom'] = trim($text);
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
