<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use ZipArchive;

class SimpleSpreadsheet
{
    /** @param array<int, string> $headers */
    /** @param array<int, array<int, string>> $rows */
    public static function toXlsx(array $headers, array $rows): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required to generate Excel files.');
        }

        $strings = [];
        $stringIndex = [];

        $registerString = static function (string $value) use (&$strings, &$stringIndex): int {
            if (array_key_exists($value, $stringIndex)) {
                return $stringIndex[$value];
            }

            $index = count($strings);
            $strings[] = $value;
            $stringIndex[$value] = $index;

            return $index;
        };

        $sheetRows = [];
        $allRows = array_merge([$headers], $rows);

        foreach ($allRows as $rowNumber => $cells) {
            $xmlCells = [];

            foreach ($cells as $columnIndex => $value) {
                $columnLetter = self::columnLetter($columnIndex);
                $excelRow = $rowNumber + 1;
                $stringIndexValue = $registerString((string) $value);
                $xmlCells[] = '<c r="' . $columnLetter . $excelRow . '" t="s"><v>' . $stringIndexValue . '</v></c>';
            }

            $sheetRows[] = '<row r="' . ($rowNumber + 1) . '">' . implode('', $xmlCells) . '</row>';
        }

        $sharedStringsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'
            . count($strings) . '" uniqueCount="' . count($strings) . '">';

        foreach ($strings as $string) {
            $sharedStringsXml .= '<si><t>' . self::xmlEscape($string) . '</t></si>';
        }

        $sharedStringsXml .= '</sst>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '</worksheet>';

        $tempFile = tempnam(sys_get_temp_dir(), 'fitcoch-xlsx-');

        if ($tempFile === false) {
            throw new RuntimeException('Unable to create temporary Excel file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::OVERWRITE) !== true) {
            @unlink($tempFile);
            throw new RuntimeException('Unable to open Excel archive.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml());
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = (string) file_get_contents($tempFile);
        @unlink($tempFile);

        return $binary;
    }

    /** @return array<int, array<int, string>> */
    public static function fromFile(string $path, string $extension): array
    {
        return match (strtolower($extension)) {
            'csv' => self::fromCsv($path),
            'xlsx' => self::fromXlsx($path),
            default => throw new RuntimeException('Unsupported spreadsheet extension.'),
        };
    }

    /** @return array<int, array<int, string>> */
    public static function fromCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to read CSV file.');
        }

        $rows = [];
        $firstLine = (string) fgets($handle);

        if ($firstLine !== '' && str_starts_with($firstLine, "\xEF\xBB\xBF")) {
            $firstLine = substr($firstLine, 3);
        }

        if ($firstLine !== '') {
            $rows[] = str_getcsv($firstLine);
        }

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_map('strval', $data);
        }

        fclose($handle);

        return $rows;
    }

    /** @return array<int, array<int, string>> */
    public static function fromXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required to read Excel files.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open Excel file.');
        }

        $sharedStrings = self::readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Excel worksheet not found.');
        }

        return self::parseSheetXml($sheetXml, $sharedStrings);
    }

    /** @return array<int, string> */
    private static function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $strings = [];

        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<int, array<int, string>>
     */
    private static function parseSheetXml(string $sheetXml, array $sharedStrings): array
    {
        $document = simplexml_load_string($sheetXml);

        if ($document === false || !isset($document->sheetData)) {
            throw new RuntimeException('Invalid Excel worksheet data.');
        }

        $rows = [];

        foreach ($document->sheetData->row as $row) {
            $cells = [];
            $columnIndex = 0;

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $targetIndex = $reference !== '' ? self::columnIndex($reference) : $columnIndex;
                $type = (string) ($cell['t'] ?? '');
                $value = '';

                if ($type === 's') {
                    $sharedIndex = (int) ($cell->v ?? 0);
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } elseif (isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $cells[$targetIndex] = $value;
                $columnIndex = $targetIndex + 1;
            }

            if ($cells === []) {
                continue;
            }

            $maxIndex = max(array_keys($cells));
            $normalized = [];

            for ($index = 0; $index <= $maxIndex; $index++) {
                $normalized[] = $cells[$index] ?? '';
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    private static function columnLetter(int $index): string
    {
        $index += 1;
        $letters = '';

        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder) . $letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    private static function columnIndex(string $cellReference): int
    {
        if (!preg_match('/^([A-Z]+)/', strtoupper($cellReference), $matches)) {
            return 0;
        }

        $letters = $matches[1];
        $index = 0;

        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '</Types>';
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';
    }

    private static function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Users" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }
}
