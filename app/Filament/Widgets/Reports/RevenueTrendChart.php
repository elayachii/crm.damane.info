<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Revenue (Last 12 Months)';

    protected function getData(): array
    {
        $rows = app(ReportQueryService::class)->revenueLast12Months(auth()->user());

        return [
            'datasets' => [[
                'label' => 'Revenue',
                'data' => array_column($rows, 'total'),
            ]],
            'labels' => array_column($rows, 'month'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
