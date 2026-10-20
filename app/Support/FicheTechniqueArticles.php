<?php

namespace App\Support;

use App\Models\Dossier;

/**
 * Articles sources pour "Fiche technique de chaque article, delivree par le
 * fabricant" : la "Description technique des fournitures/services" si elle
 * est remplie, sinon n'importe quel AUTRE document de type "bordereau" du
 * dossier qui a deja au moins un article (designation) rempli - ex:
 * "Bordereau prix unitaire", "Liste de Materiel"... Un dossier n'a pas
 * toujours de Description technique (selon le type de marche), mais a
 * presque toujours un bordereau rempli avec des designations d'articles.
 *
 * Utilise a la fois par DossierController (PDF, validation des index) et par
 * le partial de l'etape 6 (formulaire), pour ne jamais diverger.
 */
class FicheTechniqueArticles
{
    private const NOM_PREFERE = 'Description technique des fournitures/services';

    /**
     * @return array<int, array{designation: string}> index = "N°" de
     *         l'article (0-based), jamais l'id d'une BordereauLigne (ces
     *         lignes sont supprimees et recreees a chaque enregistrement du
     *         tableau source).
     */
    public static function forDossier(Dossier $dossier): array
    {
        $dossier->loadMissing(['documents.typeDocument', 'documents.bordereau.lignes']);

        $bordereauDocs = $dossier->documents
            ->filter(fn ($d) => $d->typeDocument && trim((string) $d->typeDocument->type_formulaire) === 'bordereau')
            ->sortBy('ordre')
            ->values();

        $preferred = $bordereauDocs->first(fn ($d) => trim($d->typeDocument->nom) === self::NOM_PREFERE);
        $ordered = $preferred
            ? collect([$preferred])->concat($bordereauDocs->reject(fn ($d) => $d->id === $preferred->id))
            : $bordereauDocs;

        foreach ($ordered as $doc) {
            $articles = self::articlesFromDoc($doc);
            if (collect($articles)->contains(fn ($a) => $a['designation'] !== '')) {
                return $articles;
            }
        }

        return [];
    }

    private static function articlesFromDoc($doc): array
    {
        $articles = [];
        foreach ($doc->bordereau as $bordereau) {
            foreach ($bordereau->lignes as $ligne) {
                $articles[] = ['designation' => trim((string) $ligne->designation)];
            }
        }

        return $articles;
    }
}
