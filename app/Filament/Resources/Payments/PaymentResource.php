<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use App\Modules\Payments\Services\PaymentTenantQuery;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment details')
                    ->components([
                        Select::make('subscription_id')
                            ->label('Subscription')
                            ->relationship(
                                'subscription',
                                'plan_name',
                                fn (Builder $query): Builder => PaymentTenantQuery::scopeSubscriptionsForUser($query, auth()->user())
                                    ->with('customer'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->customer?->full_name} - {$record->plan_name}")
                            ->searchable(['plan_name'])
                            ->preload()
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('currency')
                            ->required()
                            ->minLength(3)
                            ->maxLength(3)
                            ->default('MAD'),
                        Select::make('payment_method')
                            ->label('Method')
                            ->options(PaymentMethod::options())
                            ->required()
                            ->default(PaymentMethod::CASH->value),
                        TextInput::make('transaction_reference')
                            ->maxLength(255),
                        DatePicker::make('payment_date')
                            ->required()
                            ->default(today()),
                        Select::make('status')
                            ->options(PaymentStatus::options())
                            ->required()
                            ->default(PaymentStatus::PENDING->value),
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
                TextColumn::make('subscription.plan_name')
                    ->label('Subscription')
                    ->sortable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (string | float | int | null $state, Payment $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->formatStateUsing(fn (PaymentMethod | string | null $state): string => self::methodFromState($state)?->label() ?? '-'),
                TextColumn::make('transaction_reference')
                    ->label('Transaction Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (PaymentStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
            ])
            ->filters([
                Filter::make('paid')
                    ->label('Paid')
                    ->query(fn (Builder $query): Builder => $query->where('status', PaymentStatus::PAID->value)),
                Filter::make('pending')
                    ->label('Pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', PaymentStatus::PENDING->value)),
                Filter::make('failed')
                    ->label('Failed')
                    ->query(fn (Builder $query): Builder => $query->where('status', PaymentStatus::FAILED->value)),
                Filter::make('refunded')
                    ->label('Refunded')
                    ->query(fn (Builder $query): Builder => $query->where('status', PaymentStatus::REFUNDED->value)),
                Filter::make('current_month')
                    ->label('Current Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('payment_date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ])),
                Filter::make('current_year')
                    ->label('Current Year')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('payment_date', [
                            now()->startOfYear()->toDateString(),
                            now()->endOfYear()->toDateString(),
                        ])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * @return Builder<Payment>
     */
    public static function getEloquentQuery(): Builder
    {
        return PaymentTenantQuery::paymentsForUser(auth()->user())
            ->with(['agency', 'customer', 'subscription']);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }

    private static function methodFromState(PaymentMethod | string | null $state): ?PaymentMethod
    {
        if ($state instanceof PaymentMethod) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return PaymentMethod::tryFrom($state);
    }

    private static function statusFromState(PaymentStatus | string | null $state): ?PaymentStatus
    {
        if ($state instanceof PaymentStatus) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return PaymentStatus::tryFrom($state);
    }
}
