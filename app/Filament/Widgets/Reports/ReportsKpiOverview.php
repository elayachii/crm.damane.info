<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Enums\ReportDateRange;
use App\Modules\Reports\Services\ReportDateRangeFactory;
use App\Modules\Reports\Services\ReportQueryService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportsKpiOverview extends BaseWidget
{
    use ReportsWidgetAccess;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $range = app(ReportDateRangeFactory::class)->make(ReportDateRange::THIS_MONTH);
        $kpis = app(ReportQueryService::class)->kpis(auth()->user(), $range);

        return [
            Stat::make('Total Revenue', number_format((float) $kpis['total_revenue'], 2)),
            Stat::make('Monthly Revenue', number_format((float) $kpis['monthly_revenue'], 2)),
            Stat::make('Average Revenue per Customer', number_format((float) $kpis['average_revenue_per_customer'], 2)),
            Stat::make('Total Customers', (string) $kpis['total_customers']),
            Stat::make('Active Customers', (string) $kpis['active_customers']),
            Stat::make('New Customers This Month', (string) $kpis['new_customers_this_month']),
            Stat::make('Total Subscriptions', (string) $kpis['total_subscriptions']),
            Stat::make('Active Subscriptions', (string) $kpis['active_subscriptions']),
            Stat::make('Expiring Subscriptions', (string) $kpis['expiring_soon']),
            Stat::make('Total Invoices', (string) $kpis['total_invoices']),
            Stat::make('Outstanding Invoices', (string) $kpis['outstanding_invoices']),
            Stat::make('Outstanding Balance', number_format((float) $kpis['outstanding_balance'], 2)),
            Stat::make('Total Payments', (string) $kpis['total_payments']),
            Stat::make('Open Tickets', (string) $kpis['open_tickets']),
        ];
    }
}
