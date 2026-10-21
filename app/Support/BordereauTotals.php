<?php

namespace App\Support;

use App\Models\Dossier;

/**
 * Totaux HTVA des bordereaux de prix d'un dossier (fournitures et services
 * connexes), calcules directement a partir des lignes de bordereau
 * enregistrees - jamais une valeur saisie a la main qui peut devenir perimee
 * des qu'un prix change ailleurs. Utilise pour pre-remplir/resynchroniser le
 * "Tableau de resume des bordereaux de prix" et les montants de la "Lettre
 * de soumission".
 */
class BordereauTotals
{
    private const FOURNITURES_NOMS_PRIMAIRES = [
        'Bordereau des prix pour les fournitures à importer',
        'Bordereau des prix des fournitures, déjà importées',
        'Bordereau des prix pour les fournitures fabriquées au Bénin',
    ];

    private const FOURNITURES_NOMS_REPLI = [
        'Bordereau prix unitaire',
        'Listes des Fournitures et Calendrier de livraison',
        'Cadres de sous détails des prix unitaire',
    ];

    private const SERVICES_CONNEXES_NOMS = [
        "Bordereau des prix et calendrier d'exécution des services connexes",
        'Listes des services connexes et calendrier de réalisation',
    ];

    private static function sumMontantForNames(Dossier $dossier, array $names): float
    {
        $dossier->loadMissing(['documents.typeDocument', 'documents.bordereau.lignes']);

        return (float) $dossier->documents
            ->filter(fn ($doc) => in_array(optional($doc->typeDocument)->nom, $names, true))
            ->sum(fn ($doc) => $doc->bordereau->sum(fn ($bordereau) => $bordereau->lignes->sum(fn ($ligne) => (float) ($ligne->montant ?? 0))));
    }

    /**
     * Montant HTVA des fournitures : le Bordereau des prix pour les
     * fournitures a importer/deja importees/fabriquees au Benin est
     * prioritaire ; a defaut (dossier de services uniquement), on retombe
     * sur le Bordereau prix unitaire/les Listes de fournitures.
     */
    public static function fournituresHT(Dossier $dossier): float
    {
        $total = self::sumMontantForNames($dossier, self::FOURNITURES_NOMS_PRIMAIRES);
        if ($total > 0) {
            return $total;
        }

        return self::sumMontantForNames($dossier, self::FOURNITURES_NOMS_REPLI);
    }

    public static function servicesConnexesHT(Dossier $dossier): float
    {
        return self::sumMontantForNames($dossier, self::SERVICES_CONNEXES_NOMS);
    }
}
