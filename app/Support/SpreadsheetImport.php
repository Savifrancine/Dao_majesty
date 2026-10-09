<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Lit un fichier CSV, Excel (.xlsx) ou Word (.docx) uploadé et le transforme
 * en tableau brut [headers, rows] exploitable pour pré-remplir un formulaire.
 * Parseur minimal volontairement écrit sans dépendance externe
 * (ZipArchive/SimpleXML suffisent : .xlsx et .docx sont tous deux de simples
 * archives de fichiers XML). Pour un .docx, c'est le plus grand tableau du
 * document qui est utilisé ; un document sans tableau (texte libre) n'est
 * pas pris en charge ici.
 */
class SpreadsheetImport
{
    public static function parse(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => self::parseCsv($file->getRealPath()),
            'xlsx' => self::parseXlsx($file->getRealPath()),
            'docx' => self::parseDocxTable($file->getRealPath()),
            default => throw new \InvalidArgumentException('Format non supporté : ' . $extension . ' (utilisez .csv, .xlsx ou .docx)'),
        };

        $rows = array_values(array_filter($rows, fn ($row) => self::rowHasContent($row)));

        if (empty($rows)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($rows));

        return ['headers' => $headers, 'rows' => array_values($rows)];
    }

    private static function rowHasContent(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return true;
            }
        }
        return false;
    }

    private static function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $sampleLine = strtok($content, "\n") ?: '';
        $delimiter = substr_count($sampleLine, ';') > substr_count($sampleLine, ',') ? ';' : ',';

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private static function parseXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible de lire le fichier Excel.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedStrings = self::parseSharedStrings($sharedXml);
        }

        $sheetName = self::firstSheetPath($zip);
        $sheetXml = $sheetName ? $zip->getFromName($sheetName) : false;
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        return self::parseSheetXml($sheetXml, $sharedStrings);
    }

    private static function firstSheetPath(\ZipArchive $zip): ?string
    {
        if ($zip->getFromName('xl/worksheets/sheet1.xml') !== false) {
            return 'xl/worksheets/sheet1.xml';
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                return $name;
            }
        }
        return null;
    }

    private static function parseSharedStrings(string $xml): array
    {
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            return [];
        }
        $strings = [];
        foreach ($doc->si as $si) {
            $strings[] = trim(self::extractText($si));
        }
        return $strings;
    }

    private static function extractText(\SimpleXMLElement $node): string
    {
        if (isset($node->t)) {
            return (string) $node->t;
        }
        $text = '';
        foreach ($node->r as $run) {
            $text .= (string) $run->t;
        }
        return $text;
    }

    private static function parseSheetXml(string $xml, array $sharedStrings): array
    {
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            return [];
        }

        $rows = [];
        foreach ($doc->sheetData->row as $rowNode) {
            $rowIndex = (int) ($rowNode['r'] ?? 0);
            $cells = [];
            foreach ($rowNode->c as $cellNode) {
                $ref = (string) ($cellNode['r'] ?? '');
                $colIndex = self::columnIndexFromRef($ref);
                $type = (string) ($cellNode['t'] ?? '');
                $value = isset($cellNode->v) ? (string) $cellNode->v : '';

                if ($type === 's' && $value !== '') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = self::extractText($cellNode);
                }

                $cells[$colIndex] = $value;
            }

            if (empty($cells)) {
                continue;
            }

            $maxCol = max(array_keys($cells));
            $line = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $line[] = $cells[$c] ?? '';
            }

            $rows[$rowIndex > 0 ? $rowIndex : count($rows) + 1] = $line;
        }

        ksort($rows);
        return array_values($rows);
    }

    /**
     * Extrait le plus grand tableau (le plus de lignes) d'un document Word, en
     * texte brut cellule par cellule. S'il y a plusieurs tableaux, on suppose
     * que le plus grand est celui a importer (bordereau, liste...).
     */
    private static function parseDocxTable(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible de lire le fichier Word.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return [];
        }

        $dom = new \DOMDocument();
        $previousSetting = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_use_internal_errors($previousSetting);
        if (!$loaded) {
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $tables = $xpath->query('//w:tbl');
        if ($tables === false || $tables->length === 0) {
            return [];
        }

        $bestTable = null;
        $bestRowCount = 0;
        foreach ($tables as $table) {
            $rowCount = $xpath->query('.//w:tr', $table)->length;
            if ($rowCount > $bestRowCount) {
                $bestRowCount = $rowCount;
                $bestTable = $table;
            }
        }
        if (!$bestTable) {
            return [];
        }

        $rows = [];
        foreach ($xpath->query('.//w:tr', $bestTable) as $trNode) {
            $cells = [];
            foreach ($xpath->query('.//w:tc', $trNode) as $tcNode) {
                $text = '';
                foreach ($xpath->query('.//w:t', $tcNode) as $tNode) {
                    $text .= $tNode->textContent;
                }
                $cells[] = trim($text);
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private static function columnIndexFromRef(string $ref): int
    {
        if (!preg_match('/^([A-Z]+)/', $ref, $m)) {
            return 0;
        }
        $letters = $m[1];
        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }
        return $index - 1;
    }
}
