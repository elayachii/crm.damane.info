<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;

class SubscriptionReport extends BaseReportPage
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Subscription Report';

    protected static ?int $navigationSort = 83;

    protected function reportKey(): string
    {
        return 'subscription';
    }
}
