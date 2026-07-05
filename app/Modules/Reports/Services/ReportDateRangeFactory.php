<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Enums\ReportDateRange;
use App\Modules\Reports\DTOs\ReportDateRangeData;
use Carbon\CarbonImmutable;

final class ReportDateRangeFactory
{
    public function make(ReportDateRange $range, ?string $customStart = null, ?string $customEnd = null): ReportDateRangeData
    {
        $today = CarbonImmutable::today();

        return match ($range) {
            ReportDateRange::TODAY => new ReportDateRangeData($today->startOfDay(), $today->endOfDay()),
            ReportDateRange::LAST_7_DAYS => new ReportDateRangeData($today->subDays(6)->startOfDay(), $today->endOfDay()),
            ReportDateRange::LAST_30_DAYS => new ReportDateRangeData($today->subDays(29)->startOfDay(), $today->endOfDay()),
            ReportDateRange::THIS_MONTH => new ReportDateRangeData($today->startOfMonth(), $today->endOfMonth()),
            ReportDateRange::LAST_MONTH => new ReportDateRangeData($today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()),
            ReportDateRange::THIS_YEAR => new ReportDateRangeData($today->startOfYear(), $today->endOfYear()),
            ReportDateRange::CUSTOM => new ReportDateRangeData(
                CarbonImmutable::parse($customStart ?? $today->startOfMonth()->toDateString())->startOfDay(),
                CarbonImmutable::parse($customEnd ?? $today->endOfMonth()->toDateString())->endOfDay(),
            ),
        };
    }
}
