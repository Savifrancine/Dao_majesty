<?php

namespace App\Support;

use App\Models\Dossier;

/**
 * Delai de livraison/execution d'un dossier, derive directement du champ
 * "Date de livraison" (date_prestation) deja saisi dans les bordereaux de
 * prix du dossier - jamais une valeur par defaut generique. Utilise pour
 * pre-remplir/synchroniser le point "b) Delai d'execution" de la Lettre de
 * soumission : la valeur doit toujours correspondre a celle reellement
 * saisie dans les bordereaux (ex: "1 mois"), pas a un texte fige sans
 * rapport avec le dossier.
 */
class BordereauDelai
{
    private const NOM_PREFERE = 'Bordereau des prix pour les fournitures à importer';
    private const DESCRIPTION_TECHNIQUE_NOM = 'Description technique des fournitures/services';
    private const TABLEAU_RESUME_NOM = 'Tableau de résumé des bordereaux de prix';
    private const LISTE_MATERIEL_NOM = 'Liste de Matériel';

    /**
     * Documents dont "date_prestation" n'est pas une date unique mais la
     * concatenation (separee par " / ") de plusieurs colonnes de date
     * distinctes de leur propre formulaire (ex: "Listes des Fournitures et
     * Calendrier de livraison" : plus tot / plus tard / offerte). Reprendre
     * ce texte combine tel quel ailleurs (autre bordereau, Lettre de
     * soumission) afficherait a tort les 3 valeurs a la fois - voir
     * singleValueFromRaw().
     */
    private const COLONNES_MULTIPLES_NOMS = [
        'Listes des Fournitures et Calendrier de livraison',
        'Listes des services connexes et calendrier de réalisation',
    ];

    /**
     * Ramene la "date_prestation" brute d'une ligne a une seule valeur
     * utilisable ailleurs (autre bordereau a colonne "Date de livraison"
     * unique, "b) Delai d'execution" de la Lettre de soumission) : pour les
     * documents a plusieurs colonnes de date, on ne reprend JAMAIS les 3 a la
     * fois - uniquement la date "offerte" (la plus engageante pour le
     * candidat) si elle est renseignee, sinon la premiere date renseignee
     * (la "date de livraison" generale). Les autres documents (une seule
     * colonne "Date de livraison") n'ont jamais ete concatenes : leur valeur
     * est retournee telle quelle.
     */
    public static function singleValueFromRaw(?string $raw, string $docTypeName): string
    {
        $raw = trim((string) $raw);
        if ($raw === '' || !in_array(trim($docTypeName), self::COLONNES_MULTIPLES_NOMS, true)) {
            return $raw;
        }

        $parts = array_map('trim', preg_split('/\s*\/\s*/', $raw));
        $offerte = $parts[2] ?? '';
        if ($offerte !== '') {
            return $offerte;
        }

        foreach ($parts as $part) {
            if ($part !== '') {
                return $part;
            }
        }

        return '';
    }

    /**
     * @return string|null La premiere valeur "date_prestation" non vide
     *         trouvee (ordre de preference : le bordereau des fournitures a
     *         importer d'abord, sinon le premier autre bordereau rempli du
     *         dossier), ou null si aucun bordereau n'a de date renseignee.
     */
    public static function find(Dossier $dossier): ?string
    {
        $dossier->loadMissing(['documents.typeDocument', 'documents.bordereau.lignes']);

        $bordereauDocs = $dossier->documents
            ->filter(fn ($d) => $d->typeDocument
                && trim((string) $d->typeDocument->type_formulaire) === 'bordereau'
                && trim($d->typeDocument->nom) !== self::TABLEAU_RESUME_NOM
                && trim($d->typeDocument->nom) !== self::DESCRIPTION_TECHNIQUE_NOM
                && trim($d->typeDocument->nom) !== self::LISTE_MATERIEL_NOM)
            ->sortBy('ordre')
            ->values();

        $preferred = $bordereauDocs->first(fn ($d) => trim($d->typeDocument->nom) === self::NOM_PREFERE);
        $ordered = $preferred
            ? collect([$preferred])->concat($bordereauDocs->reject(fn ($d) => $d->id === $preferred->id))
            : $bordereauDocs;

        foreach ($ordered as $doc) {
            foreach ($doc->bordereau as $bordereau) {
                foreach ($bordereau->lignes as $ligne) {
                    $value = self::singleValueFromRaw((string) $ligne->date_prestation, (string) $doc->typeDocument->nom);
                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}
