<?php

declare(strict_types=1);

namespace App\Modules\Reports\DTOs;

use Carbon\CarbonImmutable;

final readonly class ReportDateRangeData
{
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
    ) {
    }
}
