<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'users'));
    }

    public function view(User $user, User $model): bool
    {
        return $this->sameAgency($user, $model)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'users'));
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'users'));
    }

    public function update(User $user, User $model): bool
    {
        return $this->sameAgency($user, $model)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'users'));
    }

    public function delete(User $user, User $model): bool
    {
        return $this->sameAgency($user, $model)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'users'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'users'));
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    private function sameAgency(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->belongsToSameAgencyAs($model);
    }
}
