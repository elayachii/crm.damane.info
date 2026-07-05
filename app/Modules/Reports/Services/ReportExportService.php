<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Enums\ReportExportFormat;
use App\Models\User;
use App\Modules\Reports\DTOs\ReportDateRangeData;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportExportService
{
    public function __construct(private readonly ReportQueryService $reports)
    {
    }

    public function export(string $report, ReportExportFormat $format, ?User $user, ReportDateRangeData $range): StreamedResponse
    {
        $rows = $this->reports->rows($report, $user, $range);
        $filename = $report . '-report-' . now()->format('Ymd-His') . '.' . $format->value;

        return match ($format) {
            ReportExportFormat::CSV => $this->tabular($rows, $filename, 'text/csv', ','),
            ReportExportFormat::EXCEL => $this->tabular($rows, $filename, 'application/vnd.ms-excel', "\t"),
            ReportExportFormat::PDF => $this->pdf($rows, $filename, ucfirst($report) . ' Report'),
        };
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function tabular(array $rows, string $filename, string $contentType, string $delimiter): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $delimiter): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]), $delimiter);

                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row), $delimiter);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => $contentType]);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function pdf(array $rows, string $filename, string $title): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $title): void {
            echo $this->buildSimplePdf($title, $rows);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function buildSimplePdf(string $title, array $rows): string
    {
        $lines = [$title, 'Generated: ' . now()->toDateTimeString(), ''];

        foreach (array_slice($rows, 0, 80) as $row) {
            $lines[] = implode(' | ', array_map(static fn (mixed $value): string => (string) $value, $row));
        }

        $content = "BT /F1 10 Tf 36 806 Td 14 TL\n";

        foreach ($lines as $line) {
            $content .= '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($line, 0, 120)) . ") Tj T*\n";
        }

        $content .= 'ET';

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length ' . strlen($content) . " >> stream\n{$content}\nendstream endobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $pdf . "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
