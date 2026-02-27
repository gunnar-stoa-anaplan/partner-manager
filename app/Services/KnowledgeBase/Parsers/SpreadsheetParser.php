<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        $spreadsheet = IOFactory::load($filePath);
        $lines = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $data = $sheet->toArray(null, true, true, true);

            if (empty($data)) {
                continue;
            }

            $headers = array_shift($data);
            $columns = array_map(fn ($v) => (string) ($v ?? ''), $headers);

            foreach ($data as $rowIdx => $row) {
                $values = array_map(fn ($v) => (string) ($v ?? ''), $row);
                $rowText = implode(' | ', array_map(
                    fn ($col, $val) => "{$col}: {$val}",
                    $columns,
                    $values,
                ));

                if (trim($rowText) !== '') {
                    $lines[] = "[{$sheetName} row {$rowIdx}] {$rowText}";
                }
            }
        }

        return implode("\n", $lines);
    }
}
