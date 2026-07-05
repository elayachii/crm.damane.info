<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions;

use App\Enums\SubscriptionStatus;
use App\Filament\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use App\Modules\Subscriptions\Services\SubscriptionTenantQuery;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?string $modelLabel = 'Subscription';

    protected static ?string $pluralModelLabel = 'Subscriptions';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription details')
                    ->components([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship(
                                'customer',
                                'full_name',
                                fn (Builder $query): Builder => SubscriptionTenantQuery::scopeCustomersForUser($query, auth()->user()),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('plan_name')
                            ->label('Plan')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options(SubscriptionStatus::options())
                            ->required()
                            ->default(SubscriptionStatus::ACTIVE->value),
                        DatePicker::make('start_date')
                            ->required()
                            ->default(today()),
                        DatePicker::make('end_date')
                            ->required()
                            ->afterOrEqual('start_date'),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix(fn (): string => 'MAD'),
                        TextInput::make('currency')
                            ->required()
                            ->minLength(3)
                            ->maxLength(3)
                            ->default('MAD'),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(4)
                            ->maxLength(5000),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan_name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->formatStateUsing(fn (int | string | null $state): string => ((int) $state) . ' days'),
                TextColumn::make('price')
                    ->formatStateUsing(fn (string | float | int | null $state, Subscription $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (SubscriptionStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (SubscriptionStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active')
                    ->query(fn (Builder $query): Builder => $query->where('status', SubscriptionStatus::ACTIVE->value)),
                Filter::make('expiring_in_7_days')
                    ->label('Expiring in 7 days')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDate('end_date', '>=', today())
                        ->whereDate('end_date', '<=', today()->addDays(7))
                        ->whereNotIn('status', [
                            SubscriptionStatus::EXPIRED->value,
                            SubscriptionStatus::SUSPENDED->value,
                        ])),
                Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query): Builder => $query->where('status', SubscriptionStatus::EXPIRED->value)),
                Filter::make('suspended')
                    ->label('Suspended')
                    ->query(fn (Builder $query): Builder => $query->where('status', SubscriptionStatus::SUSPENDED->value)),
                Filter::make('current_month')
                    ->label('Current Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('start_date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * @return Builder<Subscription>
     */
    public static function getEloquentQuery(): Builder
    {
        return SubscriptionTenantQuery::subscriptionsForUser(auth()->user())
            ->with(['customer', 'agency']);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'edit' => EditSubscription::route('/{record}/edit'),
        ];
    }

    private static function statusFromState(SubscriptionStatus | string | null $state): ?SubscriptionStatus
    {
        if ($state instanceof SubscriptionStatus) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return SubscriptionStatus::tryFrom($state);
    }
}
