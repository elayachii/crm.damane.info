<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Enums\PaymentMethod;
use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class PaymentsByMethodChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Payments by Method';

    protected function getData(): array
    {
        $data = app(ReportQueryService::class)->paymentsByMethod(auth()->user());

        return [
            'datasets' => [[
                'data' => array_values($data),
            ]],
            'labels' => array_map(
                fn (string $method): string => PaymentMethod::tryFrom($method)?->label() ?? $method,
                array_keys($data),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
