<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Modules\Payments\Services\PaymentTenantQuery;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'payments')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = PaymentTenantQuery::paymentsForUser(auth()->user());

        return [
            Stat::make('Total Revenue', number_format((float) (clone $query)
                ->where('status', PaymentStatus::PAID->value)
                ->sum('amount'), 2)),
            Stat::make('Payments This Month', (clone $query)
                ->whereBetween('payment_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->count()),
            Stat::make('Pending Payments', (clone $query)->where('status', PaymentStatus::PENDING->value)->count())
                ->color(PaymentStatus::PENDING->color()),
            Stat::make('Failed Payments', (clone $query)->where('status', PaymentStatus::FAILED->value)->count())
                ->color(PaymentStatus::FAILED->color()),
        ];
    }
}
