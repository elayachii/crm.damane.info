<?php

declare(strict_types=1);

namespace App\Modules\Reports\Policies;

use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'reports'));
    }

    public function export(User $user): bool
    {
        return $this->viewAny($user);
    }
}
