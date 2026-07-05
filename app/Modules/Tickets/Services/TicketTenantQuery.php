<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Services;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TicketTenantQuery
{
    /**
     * @return Builder<Ticket>
     */
    public static function ticketsForUser(?User $user): Builder
    {
        $query = Ticket::query();

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

    /**
     * @param Builder<User> $query
     * @return Builder<User>
     */
    public static function scopeAssignableUsersForUser(Builder $query, ?User $user): Builder
    {
        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }
}
