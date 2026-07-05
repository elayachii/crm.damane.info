<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Modules\Tickets\Services\TicketTenantQuery;
use App\Support\Authorization\PermissionRegistry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can(PermissionRegistry::for(PermissionRegistry::ACTION_VIEW_ANY, 'tickets')) ?? false;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = TicketTenantQuery::ticketsForUser(auth()->user());

        return [
            Stat::make('Open Tickets', (clone $query)->where('status', TicketStatus::OPEN->value)->count())
                ->color(TicketStatus::OPEN->color()),
            Stat::make('Assigned To Me', (clone $query)->where('assigned_to', auth()->id())->count())
                ->color('info'),
            Stat::make('High Priority', (clone $query)->whereIn('priority', [
                TicketPriority::HIGH->value,
                TicketPriority::URGENT->value,
            ])->count())
                ->color('warning'),
            Stat::make('Closed Today', (clone $query)
                ->where('status', TicketStatus::CLOSED->value)
                ->whereDate('closed_at', today())
                ->count())
                ->color(TicketStatus::CLOSED->color()),
        ];
    }
}
