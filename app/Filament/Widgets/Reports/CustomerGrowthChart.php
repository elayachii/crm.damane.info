<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class CustomerGrowthChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Customer Growth';

    protected function getData(): array
    {
        $rows = app(ReportQueryService::class)->customerGrowth(auth()->user());

        return [
            'datasets' => [[
                'label' => 'Customers',
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
