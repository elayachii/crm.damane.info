<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class InvoiceReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Invoice Report';

    protected static ?int $navigationSort = 84;

    protected function reportKey(): string
    {
        return 'invoice';
    }
}
