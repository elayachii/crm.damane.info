<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Enums\TicketStatus;
use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class TicketStatusDistributionChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Ticket Status Distribution';

    protected function getData(): array
    {
        $data = app(ReportQueryService::class)->ticketStatusDistribution(auth()->user());

        return [
            'datasets' => [[
                'data' => array_values($data),
            ]],
            'labels' => array_map(
                fn (string $status): string => TicketStatus::tryFrom($status)?->label() ?? $status,
                array_keys($data),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
