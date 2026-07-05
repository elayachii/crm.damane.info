<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;

final class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'tickets'));
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->sameAgency($user, $ticket)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW, 'tickets'));
    }

    public function create(User $user): bool
    {
        return ($user->isSuperAdmin() || $user->agency_id !== null)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_CREATE, 'tickets'));
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->sameAgency($user, $ticket)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_UPDATE, 'tickets'));
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->sameAgency($user, $ticket)
            && $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE, 'tickets'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::for(PermissionRegistry::ACTION_DELETE_ANY, 'tickets'));
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    private function sameAgency(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin() || $user->agency_id === $ticket->agency_id;
    }
}
