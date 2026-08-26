<?php

namespace App\Support;

/**
 * Format commun "Marché n° {référence} - {nom}" utilisé pour faire correspondre
 * les marchés similaires du Formulaire de qualification avec les lignes
 * "Identification du marché" du Formulaire EXP – 4.1, dans les deux sens.
 */
class MarcheIdentification
{
    public static function format(string $nom, string $reference): string
    {
        $nom = trim($nom);
        $reference = trim($reference);

        if ($reference !== '' && $nom !== '') {
            return 'Marché n° ' . $reference . ' - ' . $nom;
        }
        if ($reference !== '') {
            return 'Marché n° ' . $reference;
        }
        return $nom;
    }

    /**
     * @return array{nom: string, reference: string}
     */
    public static function parse(string $identification): array
    {
        $identification = trim($identification);

        // Le séparateur " - " (avec ses espaces) est celui posé par format() ; on
        // l'exige tel quel pour ne pas couper au milieu d'une référence contenant
        // elle-même des tirets (ex: REF-2020-09).
        if ($identification !== '' && preg_match('/^Marché n°\s*(.*?) - (.*)$/u', $identification, $matches)) {
            return ['nom' => trim($matches[2]), 'reference' => trim($matches[1])];
        }
        if ($identification !== '' && preg_match('/^Marché n°\s*(.*)$/u', $identification, $matches)) {
            return ['nom' => '', 'reference' => trim($matches[1])];
        }
        return ['nom' => $identification, 'reference' => ''];
    }
}
