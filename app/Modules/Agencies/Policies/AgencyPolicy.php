<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Policies;

use App\Models\Agency;
use App\Models\User;

final class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->agency_id !== null;
    }

    public function view(User $user, Agency $agency): bool
    {
        return $user->agency_id === $agency->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Agency $agency): bool
    {
        return $user->agency_id === $agency->id;
    }

    public function delete(User $user, Agency $agency): bool
    {
        return false;
    }

    public function restore(User $user, Agency $agency): bool
    {
        return false;
    }

    public function forceDelete(User $user, Agency $agency): bool
    {
        return false;
    }
}
