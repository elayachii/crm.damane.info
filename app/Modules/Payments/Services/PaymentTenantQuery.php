<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class PaymentTenantQuery
{
    /**
     * @return Builder<Payment>
     */
    public static function paymentsForUser(?User $user): Builder
    {
        $query = Payment::query();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }

    /**
     * @param Builder<Subscription> $query
     * @return Builder<Subscription>
     */
    public static function scopeSubscriptionsForUser(Builder $query, ?User $user): Builder
    {
        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }
}
