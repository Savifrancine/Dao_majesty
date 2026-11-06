<?php

namespace App\Support;

/**
 * "Méthodes d'exécution" et "Calendrier d'exécution" affichent le prix
 * unitaire en TTC (TVA 18%), alors que tous les autres bordereaux de prix du
 * dossier (source canonique : "Bordereau prix unitaire" et les autres
 * bordereaux de fournitures) le saisissent en HT. La reprise automatique
 * entre documents (propagation apres enregistrement, et pre-remplissage d'un
 * document encore vide) doit donc convertir le prix dans le bon sens, et ne
 * jamais le propager tel quel entre un document HT et un document TTC.
 */
class PrixUnitaireTtc
{
    public const TAUX_TVA = 0.18;
    public const MULTIPLICATEUR = 1 + self::TAUX_TVA;

    public const DOCUMENTS_TTC = ["Méthodes d'exécution", "Calendrier d'exécution"];

    public static function estDocumentTtc(?string $nomDocument): bool
    {
        return in_array(trim((string) $nomDocument), self::DOCUMENTS_TTC, true);
    }

    /**
     * @return float|null null = ne jamais propager dans ce sens (source TTC
     *         vers cible HT : la conversion inverse n'est pas prise en charge,
     *         on ne veut surtout pas ecrire un montant TTC dans un champ HT).
     */
    public static function convertir(float $prix, bool $sourceEstTtc, bool $cibleEstTtc): ?float
    {
        if ($sourceEstTtc === $cibleEstTtc) {
            return $prix;
        }
        if (!$sourceEstTtc && $cibleEstTtc) {
            return $prix * self::MULTIPLICATEUR;
        }

        return null;
    }
}
