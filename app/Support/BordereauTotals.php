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

    // "Bordereau prix unitaire" et "Cadres de sous détails des prix
    // unitaire" sont volontairement EXCLUS : leur quantité est toujours
    // figée à 1 (pas un vrai champ saisissable), donc leur "montant" ne
    // fait jamais de vrai calcul prix x quantité - juste une liste de prix.
    // Ne garder ici que des documents qui calculent reellement (voir
    // DossierController::$placeholderQuantiteDocNames pour la meme
    // distinction).
    private const FOURNITURES_NOMS_REPLI = [
        'Listes des Fournitures et Calendrier de livraison',
        "Programme d'activités",
    ];

    private const SERVICES_CONNEXES_NOMS = [
        "Bordereau des prix et calendrier d'exécution des services connexes",
        'Listes des services connexes et calendrier de réalisation',
    ];

    // Taux de l'Acompte sur Impôt assis sur les Bénéfices (AIB) applique au
    // montant HT dans le "Recapitulatif general des DQE".
    private const AIB_TAUX = 0.01;

    private static function sumMontantForNames(Dossier $dossier, array $names): float
    {
        return self::sumMontantPerName($dossier, $names)->sum();
    }

    /**
     * @return \Illuminate\Support\Collection<string,float> montant par nom de
     *         document (uniquement ceux presents dans le dossier avec un
     *         montant non nul).
     */
    private static function sumMontantPerName(Dossier $dossier, array $names): \Illuminate\Support\Collection
    {
        $dossier->loadMissing(['documents.typeDocument', 'documents.bordereau.lignes']);

        return $dossier->documents
            ->filter(fn ($doc) => in_array(optional($doc->typeDocument)->nom, $names, true))
            ->groupBy(fn ($doc) => $doc->typeDocument->nom)
            ->map(fn ($docs) => (float) $docs->sum(fn ($doc) => $doc->bordereau->sum(fn ($bordereau) => $bordereau->lignes->sum(fn ($ligne) => (float) ($ligne->montant ?? 0)))))
            ->filter(fn ($montant) => $montant > 0);
    }

    /**
     * Nom(s) du/des document(s) qui alimentent reellement fournituresHT() -
     * pour afficher, en complement du montant, d'ou il vient (colonne
     * "Source" du Tableau de resume). Vide si aucun document ne contribue.
     */
    public static function fournituresSourceLabel(Dossier $dossier): string
    {
        $parPrimaires = self::sumMontantPerName($dossier, self::FOURNITURES_NOMS_PRIMAIRES);
        if ($parPrimaires->isNotEmpty()) {
            return $parPrimaires->keys()->implode(' + ');
        }

        return self::sumMontantPerName($dossier, self::FOURNITURES_NOMS_REPLI)->keys()->implode(' + ');
    }

    public static function servicesConnexesSourceLabel(Dossier $dossier): string
    {
        return self::sumMontantPerName($dossier, self::SERVICES_CONNEXES_NOMS)->keys()->implode(' + ');
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

    /**
     * Detail, document par document, pour le "Recapitulatif general des DQE" :
     * une ligne par document "a calcul" (fournitures ou services connexes)
     * reellement present dans le dossier avec un montant non nul, avec son
     * HT, sa TVA (18%), son TTC et son AIB (1%) - jamais de valeur figee,
     * toujours recalcule a partir des lignes de bordereau actuelles.
     *
     * @return \Illuminate\Support\Collection<int,array{designation:string,ht:float,tva:float,ttc:float,aib:float}>
     */
    public static function recapitulatifDqeLignes(Dossier $dossier): \Illuminate\Support\Collection
    {
        $noms = array_values(array_unique(array_merge(
            self::FOURNITURES_NOMS_PRIMAIRES,
            self::FOURNITURES_NOMS_REPLI,
            self::SERVICES_CONNEXES_NOMS
        )));

        return self::sumMontantPerName($dossier, $noms)
            ->map(function (float $ht, string $nom) {
                $tva = round($ht * 0.18);
                $ttc = $ht + $tva;
                $aib = round($ht * self::AIB_TAUX);

                return [
                    'designation' => $nom,
                    'ht' => $ht,
                    'tva' => $tva,
                    'ttc' => $ttc,
                    'aib' => $aib,
                ];
            })
            ->values();
    }
}
