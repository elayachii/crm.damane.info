<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Enums\TicketPriority;
use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\ChartWidget;

class TicketPriorityDistributionChart extends ChartWidget
{
    use ReportsWidgetAccess;

    protected ?string $heading = 'Ticket Priority Distribution';

    protected function getData(): array
    {
        $data = app(ReportQueryService::class)->ticketPriorityDistribution(auth()->user());

        return [
            'datasets' => [[
                'data' => array_values($data),
            ]],
            'labels' => array_map(
                fn (string $priority): string => TicketPriority::tryFrom($priority)?->label() ?? $priority,
                array_keys($data),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
