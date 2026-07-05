<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Services;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SubscriptionTenantQuery
{
    /**
     * @return Builder<Subscription>
     */
    public static function subscriptionsForUser(?User $user): Builder
    {
        $query = Subscription::query();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }

    /**
     * @param Builder<Customer> $query
     * @return Builder<Customer>
     */
    public static function scopeCustomersForUser(Builder $query, ?User $user): Builder
    {
        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }
}
