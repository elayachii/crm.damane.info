<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'invoices'));
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->sameAgency($user, $invoice)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'invoices'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'invoices'));
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->sameAgency($user, $invoice)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'invoices'));
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->sameAgency($user, $invoice)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'invoices'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'invoices'));
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    private function sameAgency(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $invoice->agency_id;
    }
}
