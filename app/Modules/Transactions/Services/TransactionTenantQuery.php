<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Services;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TransactionTenantQuery
{
    /**
     * @return Builder<Transaction>
     */
    public static function transactionsForUser(?User $user): Builder
    {
        $query = Transaction::query();

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
