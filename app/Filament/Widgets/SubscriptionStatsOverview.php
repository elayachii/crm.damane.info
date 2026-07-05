<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Modules\Subscriptions\Services\SubscriptionTenantQuery;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'subscriptions')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = SubscriptionTenantQuery::subscriptionsForUser(auth()->user());

        return [
            Stat::make('Total Subscriptions', (clone $query)->count()),
            Stat::make('Active', (clone $query)->where('status', SubscriptionStatus::ACTIVE->value)->count())
                ->color(SubscriptionStatus::ACTIVE->color()),
            Stat::make('Expiring Soon', (clone $query)->where('status', SubscriptionStatus::EXPIRING_SOON->value)->count())
                ->color(SubscriptionStatus::EXPIRING_SOON->color()),
            Stat::make('Expired', (clone $query)->where('status', SubscriptionStatus::EXPIRED->value)->count())
                ->color(SubscriptionStatus::EXPIRED->color()),
        ];
    }
}
