<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class SubscriptionGrowthChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Subscription Growth';

    protected function getData(): array
    {
        $rows = app(ReportQueryService::class)->subscriptionGrowth(auth()->user());

        return [
            'datasets' => [[
                'label' => 'Subscriptions',
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
