<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Models\User;
use App\Support\Authorization\PermissionRegistry;
use App\Support\Authorization\RoleName;

final class SettingsAccessService
{
    public function canManageGlobal(?User $user): bool
    {
        return $user !== null
            && $user->isSuperAdmin()
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'settings'));
    }

    public function canManageAgency(?User $user): bool
    {
        return $user !== null
            && $user->agency_id !== null
            && ($user->isSuperAdmin() || $user->hasRole(RoleName::AGENCY_OWNER))
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'settings'));
    }
}
