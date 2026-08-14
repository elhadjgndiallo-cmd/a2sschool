<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Lecteur / écrivain XLSX minimal sans dépendance externe.
 */
class SimpleXlsx
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    public static function read(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier Excel.');
        }

        $sharedStrings = self::readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Feuille Excel introuvable.');
        }

        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            throw new RuntimeException('Format Excel invalide.');
        }

        $sheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $matrix = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowIndex = (int) ($row['r'] ?? 0);
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                [$colLetters, $lineNumber] = self::splitCellRef($ref);
                $colIndex = self::columnLettersToIndex($colLetters);
                $matrix[$lineNumber][$colIndex] = self::cellValue($cell, $sharedStrings);
            }
        }

        if (empty($matrix)) {
            return ['headers' => [], 'rows' => []];
        }

        ksort($matrix);
        $headerLine = array_key_first($matrix);
        $headerRow = $matrix[$headerLine] ?? [];
        ksort($headerRow);
        $headers = array_map(fn ($v) => self::normalizeHeader((string) $v), array_values($headerRow));

        $rows = [];
        foreach ($matrix as $lineNumber => $cells) {
            if ($lineNumber === $headerLine) {
                continue;
            }

            ksort($cells);
            $values = array_values($cells);
            if (self::rowIsEmpty($values)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($values[$index]) ? trim((string) $values[$index]) : null;
            }

            $rows[] = $assoc;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|null>>  $rows
     * @param  array<int, array{0: string, 1: string}>|null  $helpRows
     */
    public static function write(string $path, array $headers, array $rows, ?array $helpRows = null): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer le fichier Excel.');
        }

        $sheet1Rows = array_merge([$headers], $rows);
        $sheet1Xml = self::buildSheetXml($sheet1Rows);

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml(!empty($helpRows)));
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml(!empty($helpRows)));
        $zip->addFromString('xl/workbook.xml', self::workbookXml(!empty($helpRows)));
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);

        if (!empty($helpRows)) {
            $helpSheetRows = array_map(fn ($r) => [(string) $r[0], (string) $r[1]], $helpRows);
            $zip->addFromString('xl/worksheets/sheet2.xml', self::buildSheetXml($helpSheetRows));
        }

        $zip->close();
    }

    /**
     * @param  array<int, array<int, string|null>>  $rows
     */
    private static function buildSheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $line = $rowIndex + 1;
            $xml .= '<row r="' . $line . '">';
            foreach ($row as $colIndex => $value) {
                $cellRef = self::indexToColumnLetters($colIndex) . $line;
                $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    /**
     * @return array<int, string>
     */
    private static function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return [];
        }

        $strings = [];
        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
            } elseif (isset($item->r)) {
                $text = '';
                foreach ($item->r as $run) {
                    $text .= (string) $run->t;
                }
                $strings[] = $text;
            } else {
                $strings[] = '';
            }
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private static function cellValue(\SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 'inlineStr' && isset($cell->is->t)) {
            return (string) $cell->is->t;
        }

        if ($type === 's') {
            $index = (int) ($cell->v ?? 0);

            return $sharedStrings[$index] ?? null;
        }

        if (isset($cell->v)) {
            return (string) $cell->v;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private static function splitCellRef(string $ref): array
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/', $ref, $matches)) {
            return ['A', 1];
        }

        return [$matches[1], (int) $matches[2]];
    }

    private static function columnLettersToIndex(string $letters): int
    {
        $index = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private static function indexToColumnLetters(int $index): string
    {
        $letters = '';
        $index++;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    private static function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        $header = str_replace([' ', '-'], '_', $header);

        return $header;
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function rowIsEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function contentTypesXml(bool $withHelpSheet): string
    {
        $sheets = '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        if ($withHelpSheet) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $sheets
            . '</Types>';
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbookRelsXml(bool $withHelpSheet): string
    {
        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        if ($withHelpSheet) {
            $rels .= '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private static function workbookXml(bool $withHelpSheet): string
    {
        $sheets = '<sheet name="Eleves" sheetId="1" r:id="rId1"/>';
        if ($withHelpSheet) {
            $sheets .= '<sheet name="Aide" sheetId="2" r:id="rId3"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets>'
            . '</workbook>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            . '<cellXfs count="1"><xf xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }
}
