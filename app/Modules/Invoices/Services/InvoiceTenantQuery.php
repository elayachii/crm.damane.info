<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class InvoiceTenantQuery
{
    /**
     * @return Builder<Invoice>
     */
    public static function invoicesForUser(?User $user): Builder
    {
        $query = Invoice::query();

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
