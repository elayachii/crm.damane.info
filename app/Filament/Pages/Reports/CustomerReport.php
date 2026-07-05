<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class CustomerReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Customer Report';

    protected static ?int $navigationSort = 82;

    protected function reportKey(): string
    {
        return 'customer';
    }
}
