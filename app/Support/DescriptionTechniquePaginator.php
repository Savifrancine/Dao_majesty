<?php

namespace App\Support;

use Dompdf\Dompdf;

/**
 * Decoupe les lignes du tableau "Description technique des fournitures/
 * services" en groupes ("chunks") qui tiennent chacun, de maniere garantie,
 * dans le budget de hauteur d'une page - en mesurant le texte avec les
 * memes metriques de police que celles utilisees par dompdf pour le rendu
 * final (DejaVu Sans).
 *
 * Pourquoi : la pagination automatique de dompdf a l'INTERIEUR d'un grand
 * tableau n'est pas fiable des qu'une cellule approche ou depasse une page
 * de contenu (texte qui deborde par-dessus le pied de page, limite deja
 * observee et documentee dans dossiers/pdf.blade.php). dompdf place en
 * revanche toujours correctement le DEBUT d'une page / le debut d'un bloc.
 * En calculant nous-memes, par avance, combien de lignes tiennent dans une
 * page, et en coupant a cet endroit precis (un <table> distinct par
 * morceau, avec un saut de page force entre chaque), on s'appuie
 * uniquement sur ce que dompdf sait faire de maniere fiable.
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
     * @param float $firstChunkHeightReductionMm Hauteur a deduire UNIQUEMENT pour le tout premier chunk globalement
     *        rendu (celui qui partage sa page avec l'entete/logo et le titre du tableau, affiches avant - tous les
     *        chunks suivants demarrent une page neuve via page-break-before et ont droit au budget complet)
     * @return array<int,array<string,mixed>> Une liste de "chunks" prets a afficher : chaque chunk a
     *         ['numero' => int, 'suite' => bool, 'cells' => ['designation' => html, 'specifications_techniques' => html, ...]]
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

        // Marge de securite : on sous-estime volontairement l'espace
        // disponible (largeur ET hauteur) pour ne jamais friser la limite
        // reelle - mieux vaut un chunk de trop qu'un debordement.
        $safetyFactor = 0.88;

        $lineHeightMm = ($fontSizePx * $lineHeightMultiplier) * (25.4 / 96) * (1 / $safetyFactor);
        $linesPerPage = max(1, (int) floor(($pageContentHeightMm * $safetyFactor) / $lineHeightMm));
        $linesPerFirstPage = max(1, (int) floor((($pageContentHeightMm - $firstChunkHeightReductionMm) * $safetyFactor) / $lineHeightMm));

        $columnWidthsPt = [];
        foreach ($columnWidthsPercent as $key => $percent) {
            $widthMm = ($percent / 100) * $tableWidthMm - (2 * $cellPaddingMm);
            $columnWidthsPt[$key] = max(10, $widthMm * $mmToPt * $safetyFactor);
        }

        $chunks = [];
        $numero = 0;
        $isVeryFirstChunk = true;
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

            // Seul le tout premier chunk de tout le tableau partage sa page
            // avec l'entete/titre ; son budget de lignes est donc reduit
            // d'autant. Les chunks suivants (y compris le 1er chunk des
            // autres articles, qui demarrent chacun une page neuve) ont
            // droit au budget plein.
            $budgetFirst = $isVeryFirstChunk ? $linesPerFirstPage : $linesPerPage;
            $firstSliceLen = max(1, $budgetFirst);
            $remainingAfterFirst = max(0, $maxLines - $firstSliceLen);
            $chunkCount = 1 + ($remainingAfterFirst > 0 ? (int) ceil($remainingAfterFirst / $linesPerPage) : 0);

            $offset = 0;
            for ($c = 0; $c < $chunkCount; $c++) {
                $len = ($c === 0) ? $firstSliceLen : $linesPerPage;
                $cells = [];
                foreach ($wrapped as $key => $lines) {
                    $slice = array_slice($lines, $offset, $len);
                    $cells[$key] = implode('<br>', array_map(fn ($l) => e($l), $slice));
                }
                $offset += $len;

                $chunks[] = [
                    'numero' => $numero,
                    'suite' => $c > 0,
                    'cells' => $cells,
                ];

                $isVeryFirstChunk = false;
            }
        }

        return $chunks;
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
