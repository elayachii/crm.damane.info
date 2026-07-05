<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'transactions'));
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->sameAgency($user, $transaction)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'transactions'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'transactions'));
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->sameAgency($user, $transaction)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'transactions'));
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->sameAgency($user, $transaction)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'transactions'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'transactions'));
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return false;
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return false;
    }

    private function sameAgency(User $user, Transaction $transaction): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $transaction->agency_id;
    }
}
