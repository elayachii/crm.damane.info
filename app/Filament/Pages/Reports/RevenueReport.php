<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class RevenueReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Revenue Report';

    protected static ?int $navigationSort = 81;

    protected function reportKey(): string
    {
        return 'revenue';
    }
}
