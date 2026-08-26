<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Lit un fichier CSV ou Excel (.xlsx) uploadé et le transforme en tableau
 * brut [headers, rows] exploitable pour pré-remplir un formulaire. Parseur
 * minimal volontairement écrit sans dépendance externe (ZipArchive/SimpleXML
 * suffisent à lire un .xlsx, qui est une simple archive de fichiers XML).
 */
class SpreadsheetImport
{
    public static function parse(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => self::parseCsv($file->getRealPath()),
            'xlsx' => self::parseXlsx($file->getRealPath()),
            default => throw new \InvalidArgumentException('Format non supporté : ' . $extension . ' (utilisez .csv ou .xlsx)'),
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
