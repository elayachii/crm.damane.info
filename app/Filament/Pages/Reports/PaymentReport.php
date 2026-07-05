<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class PaymentReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payment Report';

    protected static ?int $navigationSort = 85;

    protected function reportKey(): string
    {
        return 'payment';
    }
}
