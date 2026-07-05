<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class TicketReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationLabel = 'Ticket Report';

    protected static ?int $navigationSort = 86;

    protected function reportKey(): string
    {
        return 'ticket';
    }
}
