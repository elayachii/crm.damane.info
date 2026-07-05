<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Concerns\HasUuid;
use App\Modules\Tickets\Services\TicketNumberGenerator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'ticket_number',
    'customer_id',
    'agency_id',
    'assigned_to',
    'created_by',
    'subject',
    'description',
    'priority',
    'status',
    'opened_at',
    'closed_at',
])]
class Ticket extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(static function (Ticket $ticket): void {
            $user = auth()->user();

            if (blank($ticket->ticket_number)) {
                $ticket->ticket_number = app(TicketNumberGenerator::class)->generate();
            }

            if ($user !== null && blank($ticket->created_by)) {
                $ticket->created_by = $user->id;
            }

            if (blank($ticket->opened_at)) {
                $ticket->opened_at = now();
            }
        });

        static::saving(static function (Ticket $ticket): void {
            $ticket->assignAgencyFromCustomer();
            $ticket->validateAssignee();
            $ticket->syncClosedAt();
        });
    }

    /**
     * @return BelongsTo<Customer, Ticket>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Agency, Ticket>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return BelongsTo<User, Ticket>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<User, Ticket>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    private function assignAgencyFromCustomer(): void
    {
        if (blank($this->customer_id)) {
            return;
        }

        $customer = Customer::query()->find($this->customer_id);

        if ($customer === null) {
            return;
        }

        $user = auth()->user();

        if ($user !== null && ! $user->isSuperAdmin() && $user->agency_id !== $customer->agency_id) {
            throw new AuthorizationException('The selected customer does not belong to your agency.');
        }

        $this->agency_id = $customer->agency_id;
    }

    private function validateAssignee(): void
    {
        if (blank($this->assigned_to)) {
            return;
        }

        $assignee = User::query()->find($this->assigned_to);

        if ($assignee === null) {
            return;
        }

        if ((int) $assignee->agency_id !== (int) $this->agency_id) {
            throw new AuthorizationException('Tickets can only be assigned to users from the same agency.');
        }
    }

    private function syncClosedAt(): void
    {
        if ($this->isClosed() && blank($this->closed_at)) {
            $this->closed_at = now();
            return;
        }

        if (! $this->isClosed()) {
            $this->closed_at = null;
        }
    }

    private function isClosed(): bool
    {
        if ($this->status instanceof TicketStatus) {
            return $this->status === TicketStatus::CLOSED;
        }

        return $this->status === TicketStatus::CLOSED->value;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
