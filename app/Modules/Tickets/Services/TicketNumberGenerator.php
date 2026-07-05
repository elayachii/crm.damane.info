<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;

final class TicketNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'TKT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (Ticket::query()->withTrashed()->where('ticket_number', $number)->exists());

        return $number;
    }
}
