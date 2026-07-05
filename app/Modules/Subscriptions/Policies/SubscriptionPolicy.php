<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Policies;

use App\Models\Subscription;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'subscriptions'));
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $this->sameAgency($user, $subscription)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'subscriptions'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'subscriptions'));
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $this->sameAgency($user, $subscription)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'subscriptions'));
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $this->sameAgency($user, $subscription)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'subscriptions'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'subscriptions'));
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return false;
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return false;
    }

    private function sameAgency(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $subscription->agency_id;
    }
}
