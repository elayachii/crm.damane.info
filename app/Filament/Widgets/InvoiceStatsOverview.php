<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Modules\Invoices\Services\InvoiceTenantQuery;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'invoices')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = InvoiceTenantQuery::invoicesForUser(auth()->user());

        return [
            Stat::make('Total Invoices', (clone $query)->count()),
            Stat::make('Outstanding Balance', number_format((float) (clone $query)->sum('balance_due'), 2))
                ->color('warning'),
            Stat::make('Paid This Month', (clone $query)
                ->where('status', InvoiceStatus::PAID->value)
                ->whereBetween('issue_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->count())
                ->color('success'),
            Stat::make('Overdue Invoices', (clone $query)->where('status', InvoiceStatus::OVERDUE->value)->count())
                ->color('danger'),
        ];
    }
}
