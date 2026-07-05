<?php

declare(strict_types=1);

namespace App\Modules\Customers\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'customers'));
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->sameAgency($user, $customer)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'customers'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'customers'));
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->sameAgency($user, $customer)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'customers'));
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->sameAgency($user, $customer)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'customers'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'customers'));
    }

    public function restore(User $user, Customer $customer): bool
    {
        return false;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }

    private function sameAgency(User $user, Customer $customer): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $customer->agency_id;
    }
}
