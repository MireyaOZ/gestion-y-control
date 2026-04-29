<?php

namespace App\Support;

use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelXmlExporter
{
    private const WIDE_TEXT_HEADERS = ['detalle', 'descripcion', 'descripción', 'jerarquia', 'jerarquía'];

    public static function download(string $filenameBase, string $sheetName, array $metadataRows, array $headers, array $rows): Response
    {
        $content = self::buildWorkbook($sheetName, $metadataRows, $headers, $rows);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public static function plainText(?string $value): string
    {
        $value ??= '';
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<\/p>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<\/li>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<li[^>]*>/i', '- ', $value) ?? $value;
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace("/\r\n|\r/u", "\n", $value) ?? $value;
        $value = preg_replace("/\n{3,}/u", "\n\n", $value) ?? $value;

        return trim($value);
    }

    private static function buildWorkbook(string $sheetName, array $metadataRows, array $headers, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(self::sanitizeWorksheetName($sheetName));
        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        $maxColumns = max(array_map('count', array_merge([$headers], $rows, [[null, null]])));
        $lastColumn = Coordinate::stringFromColumnIndex(max($maxColumns, 1));

        $currentRow = 1;

        $sheet->setCellValue('A'.$currentRow, $sheetName);
        $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
        $sheet->getStyle('A'.$currentRow)->getFont()
            ->setBold(true)
            ->setSize(18)
            ->setName('Calibri')
            ->getColor()->setARGB('FF960018');
        $sheet->getStyle('A'.$currentRow.':'.$lastColumn.$currentRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($currentRow)->setRowHeight(28);

        $currentRow += 2;

        foreach ($metadataRows as [$label, $value]) {
            $normalizedValue = self::normalizeValue($value);

            if ($normalizedValue === '') {
                continue;
            }

            $sheet->setCellValue('A'.$currentRow, (string) $label);
            $sheet->setCellValue('B'.$currentRow, $normalizedValue);
            $sheet->getStyle('A'.$currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A'.$currentRow.':B'.$currentRow)->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setWrapText(true);

            $currentRow++;
        }

        if ($currentRow > 1) {
            $currentRow++;
        }

        foreach (array_values($headers) as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column.$currentRow, $header);
        }

        $tableStartRow = $currentRow;

        $headerRange = 'A'.$currentRow.':'.Coordinate::stringFromColumnIndex(max(count($headers), 1)).$currentRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        $currentRow++;

        if ($rows === []) {
            $rows = [[count($headers) > 0 ? 'No hay datos para el reporte.' : 'Sin datos']];
        }

        foreach ($rows as $row) {
            foreach (array_values($row) as $index => $cell) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                self::writeCell($sheet, $column.$currentRow, $cell);
            }

            $rowRange = 'A'.$currentRow.':'.Coordinate::stringFromColumnIndex(max(count($row), 1)).$currentRow;
            $sheet->getStyle($rowRange)->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setWrapText(true);

            $currentRow++;
        }

        $tableEndRow = $currentRow - 1;
        self::applyTableBorders(
            $sheet,
            'A'.$tableStartRow.':'.Coordinate::stringFromColumnIndex(max($maxColumns, 1)).$tableEndRow,
        );

        self::configureColumns($sheet, $metadataRows, $headers, $rows, $maxColumns);
        self::configurePrintLayout($sheet, $maxColumns);

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $content === false ? '' : $content;
    }

    private static function writeCell($sheet, string $coordinate, mixed $value): void
    {
        if (is_int($value) || is_float($value)) {
            $sheet->setCellValueExplicit($coordinate, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            return;
        }

        $sheet->setCellValue($coordinate, self::normalizeValue($value));
    }

    private static function applyTableBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FFCBD5E1');
    }

    private static function configureColumns($sheet, array $metadataRows, array $headers, array $rows, int $maxColumns): void
    {
        for ($index = 1; $index <= max($maxColumns, 1); $index++) {
            $header = (string) ($headers[$index - 1] ?? '');
            $column = Coordinate::stringFromColumnIndex($index);
            $sheet->getColumnDimension($column)->setAutoSize(false);
            $sheet->getColumnDimension($column)->setWidth(self::determineColumnWidth($header, $metadataRows, $rows, $index - 1));
        }
    }

    private static function determineColumnWidth(string $header, array $metadataRows, array $rows, int $columnIndex): float
    {
        $normalizedHeader = mb_strtolower(trim($header), 'UTF-8');
        $maxLineLength = max(8, self::longestLineLength($header));

        if ($columnIndex <= 1) {
            foreach ($metadataRows as $metadataRow) {
                $value = self::normalizeValue($metadataRow[$columnIndex] ?? '');
                $maxLineLength = max($maxLineLength, self::longestLineLength($value));
            }
        }

        foreach ($rows as $row) {
            $value = self::normalizeValue($row[$columnIndex] ?? '');
            $maxLineLength = max($maxLineLength, self::longestLineLength($value));
        }

        if ($columnIndex === 0 && $normalizedHeader === 'no.') {
            return min(max($maxLineLength + 2, 20), 28);
        }

        if ($normalizedHeader === 'nombre del sistema') {
            return min(max($maxLineLength + 4, 26), 34);
        }

        if (in_array($normalizedHeader, ['fecha de solicitud', 'fecha de creación', 'fecha'], true)) {
            return 18;
        }

        if ($normalizedHeader === 'estatus') {
            return min(max((int) ceil($maxLineLength * 0.7), 28), 42);
        }

        if ($columnIndex === 1 && str_contains($normalizedHeader, 'fecha')) {
            return min(max($maxLineLength + 2, 18), 26);
        }

        if (in_array($normalizedHeader, self::WIDE_TEXT_HEADERS, true)) {
            return min(max($maxLineLength * 0.85, 22), 42);
        }

        if (str_contains($normalizedHeader, 'fecha')) {
            return 14;
        }

        if (in_array($normalizedHeader, ['no.', 'nivel', 'avance'], true)) {
            return 10;
        }

        return min(max($maxLineLength + 2, 12), 24);
    }

    private static function longestLineLength(string $value): int
    {
        $lines = preg_split('/\R/u', $value) ?: [''];

        return max(array_map(
            static fn (string $line): int => mb_strlen($line, 'UTF-8'),
            $lines,
        ));
    }

    private static function configurePrintLayout($sheet, int $maxColumns): void
    {
        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        $pageSetup->setOrientation($maxColumns > 6
            ? \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            : \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

        $sheet->getPageMargins()
            ->setTop(0.4)
            ->setRight(0.25)
            ->setLeft(0.25)
            ->setBottom(0.4);
    }

    private static function normalizeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return trim(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    private static function sanitizeWorksheetName(string $sheetName): string
    {
        $sheetName = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName) ?? $sheetName;
        $sheetName = trim($sheetName);

        if ($sheetName === '') {
            $sheetName = 'Reporte';
        }

        return mb_substr($sheetName, 0, 31);
    }

}