<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Reports\Concerns\AuthorizesReports;
use App\Filament\Widgets\Reports\CustomerGrowthChart;
use App\Filament\Widgets\Reports\PaymentsByMethodChart;
use App\Filament\Widgets\Reports\ReportsKpiOverview;
use App\Filament\Widgets\Reports\RevenueTrendChart;
use App\Filament\Widgets\Reports\SubscriptionGrowthChart;
use App\Filament\Widgets\Reports\TicketPriorityDistributionChart;
use App\Filament\Widgets\Reports\TicketStatusDistributionChart;
use BackedEnum;
use Filament\Pages\Page;

class ReportsDashboard extends Page
{
    use AuthorizesReports;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.reports.dashboard';

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ReportsKpiOverview::class,
            RevenueTrendChart::class,
            CustomerGrowthChart::class,
            SubscriptionGrowthChart::class,
            PaymentsByMethodChart::class,
            TicketStatusDistributionChart::class,
            TicketPriorityDistributionChart::class,
        ];
    }
}
