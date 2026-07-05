<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportDateRange: string
{
    case TODAY = 'today';
    case LAST_7_DAYS = 'last_7_days';
    case LAST_30_DAYS = 'last_30_days';
    case THIS_MONTH = 'this_month';
    case LAST_MONTH = 'last_month';
    case THIS_YEAR = 'this_year';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::TODAY => 'Today',
            self::LAST_7_DAYS => 'Last 7 Days',
            self::LAST_30_DAYS => 'Last 30 Days',
            self::THIS_MONTH => 'This Month',
            self::LAST_MONTH => 'Last Month',
            self::THIS_YEAR => 'This Year',
            self::CUSTOM => 'Custom Date Range',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $range): array => [$range->value => $range->label()])
            ->all();
    }
}
