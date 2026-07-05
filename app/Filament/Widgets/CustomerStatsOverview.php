<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class CustomerStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'customers')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = $this->baseCustomerQuery();

        return [
            Stat::make('Total Customers', (clone $query)->count()),
            Stat::make('Active Customers', (clone $query)->where('status', CustomerStatus::ACTIVE->value)->count())
                ->color(CustomerStatus::ACTIVE->color()),
            Stat::make('Suspended Customers', (clone $query)->where('status', CustomerStatus::SUSPENDED->value)->count())
                ->color(CustomerStatus::SUSPENDED->color()),
            Stat::make('Trial Customers', (clone $query)->where('status', CustomerStatus::TRIAL->value)->count())
                ->color(CustomerStatus::TRIAL->color()),
        ];
    }

    /**
     * @return Builder<Customer>
     */
    private function baseCustomerQuery(): Builder
    {
        $user = auth()->user();
        $query = Customer::query();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }
}
