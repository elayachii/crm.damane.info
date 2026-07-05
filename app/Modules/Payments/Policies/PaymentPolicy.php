<?php

declare(strict_types=1);

namespace App\Modules\Payments\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'payments'));
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->sameAgency($user, $payment)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'payments'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'payments'));
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->sameAgency($user, $payment)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'payments'));
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->sameAgency($user, $payment)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'payments'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'payments'));
    }

    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }

    private function sameAgency(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $payment->agency_id;
    }
}
