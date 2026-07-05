<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportExportFormat: string
{
    case CSV = 'csv';
    case EXCEL = 'xls';
    case PDF = 'pdf';
}
