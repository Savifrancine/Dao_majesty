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
                && trim($d->typeDocument->nom) !== self::DESCRIPTION_TECHNIQUE_NOM)
            ->sortBy('ordre')
            ->values();

        $preferred = $bordereauDocs->first(fn ($d) => trim($d->typeDocument->nom) === self::NOM_PREFERE);
        $ordered = $preferred
            ? collect([$preferred])->concat($bordereauDocs->reject(fn ($d) => $d->id === $preferred->id))
            : $bordereauDocs;

        foreach ($ordered as $doc) {
            foreach ($doc->bordereau as $bordereau) {
                foreach ($bordereau->lignes as $ligne) {
                    $value = trim((string) $ligne->date_prestation);
                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}
