<?php

declare(strict_types=1);

namespace App\Modules\Settings\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'settings'));
    }

    public function view(User $user, Setting $setting): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'settings'));
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'settings'));
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->isSuperAdmin()
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'settings'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin()
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'settings'));
    }
}
