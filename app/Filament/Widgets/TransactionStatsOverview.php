<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransferOperator;
use App\Modules\Transactions\Services\TransactionTenantQuery;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TransactionStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'transactions')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = TransactionTenantQuery::transactionsForUser(auth()->user());
        $todayQuery = (clone $query)->whereDate('transaction_date', today());
        $operatorTotals = (clone $query)
            ->select('operator', DB::raw('COUNT(*) as total'))
            ->groupBy('operator')
            ->pluck('total', 'operator')
            ->map(fn (mixed $total, string $operator): string => (TransferOperator::tryFrom($operator)?->label() ?? $operator) . ': ' . (int) $total)
            ->values()
            ->implode(' | ');

        return [
            Stat::make('Transactions Today', (clone $todayQuery)->count()),
            Stat::make('Transactions This Month', (clone $query)
                ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count()),
            Stat::make('Total Amount Today', number_format((float) (clone $todayQuery)->sum('total_amount'), 2)),
            Stat::make('Total Fees Today', number_format((float) (clone $todayQuery)->sum('fees'), 2)),
            Stat::make('Total Transactions by Operator', $operatorTotals !== '' ? $operatorTotals : 'No transactions'),
        ];
    }
}
