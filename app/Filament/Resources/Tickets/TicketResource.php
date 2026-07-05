<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Modules\Tickets\Services\TicketTenantQuery;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?string $modelLabel = 'Ticket';

    protected static ?string $pluralModelLabel = 'Tickets';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket details')
                    ->components([
                        TextInput::make('ticket_number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship(
                                'customer',
                                'full_name',
                                fn (Builder $query): Builder => TicketTenantQuery::scopeCustomersForUser($query, auth()->user()),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Assigned To')
                            ->relationship(
                                'assignee',
                                'name',
                                fn (Builder $query): Builder => TicketTenantQuery::scopeAssignableUsersForUser($query, auth()->user()),
                            )
                            ->searchable()
                            ->preload(),
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Select::make('priority')
                            ->options(TicketPriority::options())
                            ->required()
                            ->default(TicketPriority::MEDIUM->value),
                        Select::make('status')
                            ->options(TicketStatus::options())
                            ->required()
                            ->default(TicketStatus::OPEN->value),
                        DateTimePicker::make('opened_at')
                            ->seconds(false)
                            ->default(now()),
                        DateTimePicker::make('closed_at')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5)
                            ->maxLength(10000),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (TicketPriority | string | null $state): string => self::priorityFromState($state)?->label() ?? '-')
                    ->color(fn (TicketPriority | string | null $state): string => self::priorityFromState($state)?->color() ?? 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (TicketStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (TicketStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('open')
                    ->label('Open')
                    ->query(fn (Builder $query): Builder => $query->where('status', TicketStatus::OPEN->value)),
                Filter::make('in_progress')
                    ->label('In Progress')
                    ->query(fn (Builder $query): Builder => $query->where('status', TicketStatus::IN_PROGRESS->value)),
                Filter::make('waiting_customer')
                    ->label('Waiting Customer')
                    ->query(fn (Builder $query): Builder => $query->where('status', TicketStatus::WAITING_CUSTOMER->value)),
                Filter::make('resolved')
                    ->label('Resolved')
                    ->query(fn (Builder $query): Builder => $query->where('status', TicketStatus::RESOLVED->value)),
                Filter::make('closed')
                    ->label('Closed')
                    ->query(fn (Builder $query): Builder => $query->where('status', TicketStatus::CLOSED->value)),
                Filter::make('high_priority')
                    ->label('High Priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', [
                        TicketPriority::HIGH->value,
                        TicketPriority::URGENT->value,
                    ])),
                Filter::make('current_month')
                    ->label('Current Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('created_at', [
                            now()->startOfMonth(),
                            now()->endOfMonth(),
                        ])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * @return Builder<Ticket>
     */
    public static function getEloquentQuery(): Builder
    {
        return TicketTenantQuery::ticketsForUser(auth()->user())
            ->with(['agency', 'assignee', 'creator', 'customer']);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    private static function priorityFromState(TicketPriority | string | null $state): ?TicketPriority
    {
        if ($state instanceof TicketPriority) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return TicketPriority::tryFrom($state);
    }

    private static function statusFromState(TicketStatus | string | null $state): ?TicketStatus
    {
        if ($state instanceof TicketStatus) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return TicketStatus::tryFrom($state);
    }
}
