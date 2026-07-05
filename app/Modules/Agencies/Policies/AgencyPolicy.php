<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Policies;

use App\Models\Agency;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'agencies'));
    }

    public function view(User $user, Agency $agency): bool
    {
        return $this->sameAgency($user, $agency)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'agencies'));
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'agencies'));
    }

    public function update(User $user, Agency $agency): bool
    {
        return $this->sameAgency($user, $agency)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'agencies'));
    }

    public function delete(User $user, Agency $agency): bool
    {
        return $this->sameAgency($user, $agency)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'agencies'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin()
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'agencies'));
    }

    public function restore(User $user, Agency $agency): bool
    {
        return false;
    }

    public function forceDelete(User $user, Agency $agency): bool
    {
        return false;
    }

    private function sameAgency(User $user, Agency $agency): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $agency->id;
    }
}
