<?php

namespace App\Support;

use Dompdf\Dompdf;

/**
 * Decoupe les lignes du tableau "Description technique des fournitures/
 * services" en PAGES, chacune garantie, de maniere fiable, de tenir dans le
 * budget de hauteur d'une page - en mesurant le texte avec les memes
 * metriques de police que celles utilisees par dompdf pour le rendu final
 * (DejaVu Sans). Plusieurs lignes courtes partagent la meme page (pas une
 * page par ligne) ; une ligne dont le contenu depasse a elle seule une page
 * entiere est coupee sur plusieurs pages, au bon endroit.
 *
 * Pourquoi calculer nous-memes : la pagination automatique de dompdf a
 * l'INTERIEUR d'un grand tableau n'est pas fiable des qu'une cellule
 * approche ou depasse une page de contenu (texte qui deborde par-dessus le
 * pied de page, limite deja observee et documentee dans dossiers/
 * pdf.blade.php). dompdf place en revanche toujours correctement le DEBUT
 * d'une page / le debut d'un bloc. En calculant nous-memes, par avance,
 * combien de lignes de texte tiennent dans une page, et en coupant a cet
 * endroit precis (un <table> distinct par page, avec un saut de page force
 * avant chacune), on s'appuie uniquement sur ce que dompdf sait faire de
 * maniere fiable.
 */
class DescriptionTechniquePaginator
{
    private const FONT_FAMILY = 'DejaVu Sans';

    /**
     * @param iterable $lignes Les BordereauLigne a paginer (dans l'ordre)
     * @param array<string,float> $columnWidthsPercent Largeur de chaque colonne texte, en % de la largeur du tableau (hors colonne N°)
     * @param float $tableWidthMm Largeur totale du tableau (= largeur utile de la page)
     * @param float $cellPaddingMm Rembourrage (padding) applique de chaque cote d'une cellule
     * @param float $fontSizePx Taille de police CSS (en px) utilisee dans le tableau
     * @param float $lineHeightMultiplier Interligne CSS (ex: 1.25)
     * @param float $pageContentHeightMm Hauteur utile disponible par page pour ce tableau (marges deja deduites)
     * @param float $firstChunkHeightReductionMm Hauteur a deduire UNIQUEMENT pour la toute premiere page globalement
     *        rendue (celle qui partage sa page avec l'entete/logo et le titre du tableau, affiches avant - toutes
     *        les pages suivantes demarrent une page neuve via page-break-before et ont droit au budget complet)
     * @return array<int,array<string,mixed>> Une liste de "pages" pretes a afficher : chaque page a
     *         ['rows' => [['numero'=>int, 'suite'=>bool, 'cells'=>['designation'=>html, ...]], ...]]
     */
    public static function chunk(
        iterable $lignes,
        array $columnWidthsPercent,
        float $tableWidthMm,
        float $cellPaddingMm,
        float $fontSizePx,
        float $lineHeightMultiplier,
        float $pageContentHeightMm,
        float $firstChunkHeightReductionMm = 0.0
    ): array {
        $dompdf = new Dompdf();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont(self::FONT_FAMILY, 'normal');

        // CSS px -> points PDF (96dpi -> 72dpi), meme conversion que celle
        // que dompdf applique lui-meme au rendu.
        $fontSizePt = $fontSizePx * 0.75;
        $mmToPt = 2.83465;

        // Marge de securite pour la LARGEUR (mesure du texte) : sous-estime
        // volontairement l'espace disponible pour ne jamais friser la
        // limite reelle du retour a la ligne.
        $safetyFactor = 0.88;

        // Marge de securite DEDIEE A LA HAUTEUR, distincte de celle ci-dessus :
        // chaque <tr> a un cout fixe (padding + bordure de cellule, + le
        // <thead> repete sur chaque page) que le calcul "police x interligne"
        // seul ne capture pas - negligeable sur un article tres long (1 seul
        // <tr> pour des dizaines de lignes), mais significatif des que
        // beaucoup d'articles courts (1-2 lignes) s'enchainent, chacun son
        // propre <tr>. Valeur etalonnee empiriquement : 0.88 laissait deborder
        // ~8 lignes par page de trop sur une page supplementaire a moitie
        // vide ; 0.63 etait fiable mais, combine au budget de page de
        // l'epoque (150mm, tres en-deca du plafond theorique), laissait
        // beaucoup de place inutilisee en bas de chaque page (cf. parametre
        // $pageContentHeightMm cote appelant). Reteste le 2026-10-20 en meme
        // temps que ce budget de page : des valeurs plus genereuses (jusqu'a
        // 0.80) ont semble sures sur des scenarios isoles (40 articles tres
        // courts, 40 articles tres longs, tous deux en rendu continu "sans
        // fusion"), mais ont fini par produire un VRAI chevauchement texte/
        // numero de page sur un document realiste passant par le rendu
        // "fusion" (plusieurs documents merges via FPDI, chacun dans sa
        // propre instance Dompdf isolee - visiblement plus sensible que le
        // rendu continu a ces reglages). 0.65 a ete revalide SANS aucun
        // chevauchement sur ces 3 scenarios (voir aussi le commentaire sur
        // $pageContentHeightMm cote appelant dans pdf.blade.php). Ne pas
        // augmenter sans retester explicitement le scenario "fusion", qui
        // est celui qui a revele le probleme - les scenarios en rendu
        // continu seuls ne suffisent pas a le detecter.
        $heightSafetyFactor = 0.65;

        $lineHeightMm = ($fontSizePx * $lineHeightMultiplier) * (25.4 / 96) * (1 / $safetyFactor);
        $linesPerPage = max(1, (int) floor(($pageContentHeightMm * $heightSafetyFactor) / $lineHeightMm));
        $linesPerFirstPage = max(1, (int) floor((($pageContentHeightMm - $firstChunkHeightReductionMm) * $heightSafetyFactor) / $lineHeightMm));

        $columnWidthsPt = [];
        foreach ($columnWidthsPercent as $key => $percent) {
            $widthMm = ($percent / 100) * $tableWidthMm - (2 * $cellPaddingMm);
            $columnWidthsPt[$key] = max(10, $widthMm * $mmToPt * $safetyFactor);
        }

        $pages = [];
        $currentPageRows = [];
        $remainingOnPage = $linesPerFirstPage;
        $numero = 0;

        $flushPage = function () use (&$pages, &$currentPageRows) {
            if (!empty($currentPageRows)) {
                $pages[] = ['rows' => $currentPageRows];
                $currentPageRows = [];
            }
        };

        foreach ($lignes as $ligne) {
            $numero++;

            $wrapped = [];
            foreach ($columnWidthsPercent as $key => $percent) {
                $raw = (string) ($ligne->{$key} ?? '');
                $wrapped[$key] = self::wrapTextLines($raw, $font, $fontSizePt, $columnWidthsPt[$key], $fontMetrics);
            }

            $maxLines = 1;
            foreach ($wrapped as $lines) {
                $maxLines = max($maxLines, count($lines));
            }

            $offset = 0;
            $isFirstSliceOfLigne = true;
            while ($offset < $maxLines) {
                if ($remainingOnPage <= 0) {
                    $flushPage();
                    $remainingOnPage = $linesPerPage;
                }

                $take = min($remainingOnPage, $maxLines - $offset);
                $cells = [];
                foreach ($wrapped as $key => $lines) {
                    $slice = array_slice($lines, $offset, $take);
                    $cells[$key] = implode('<br>', array_map(fn ($l) => e($l), $slice));
                }

                $currentPageRows[] = [
                    'numero' => $numero,
                    'suite' => !$isFirstSliceOfLigne,
                    'cells' => $cells,
                ];

                $offset += $take;
                $remainingOnPage -= $take;
                $isFirstSliceOfLigne = false;
            }
        }

        $flushPage();

        return $pages;
    }

    /**
     * Decoupe un texte (eventuellement multi-paragraphes, separes par des
     * retours a la ligne) en lignes physiques telles qu'elles seront
     * rendues, en mesurant chaque mot avec les metriques de police
     * fournies - le meme algorithme (glouton, mot par mot) que celui
     * qu'utilise un moteur de rendu HTML pour le retour a la ligne normal.
     *
     * @return string[] Lignes de texte BRUT (non-echappees HTML)
     */
    private static function wrapTextLines(string $text, $font, float $fontSizePt, float $maxWidthPt, $fontMetrics): array
    {
        $paragraphs = preg_split('/\r\n|\r|\n/', trim($text));
        if ($paragraphs === false) {
            $paragraphs = [$text];
        }

        $lines = [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $words = preg_split('/\s+/', $paragraph) ?: [$paragraph];
            $current = '';
            foreach ($words as $word) {
                $candidate = $current === '' ? $word : $current . ' ' . $word;
                $width = $fontMetrics->getTextWidth($candidate, $font, $fontSizePt);
                if ($width > $maxWidthPt && $current !== '') {
                    $lines[] = $current;
                    $current = $word;
                } else {
                    $current = $candidate;
                }
            }
            if ($current !== '') {
                $lines[] = $current;
            }
        }

        return $lines ?: [''];
    }
}
