<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\TransferOperator;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use App\Modules\Transactions\Services\TransactionTenantQuery;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $pluralModelLabel = 'Transactions';

    protected static ?int $navigationSort = 55;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transfer history')
                    ->components([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship(
                                'customer',
                                'full_name',
                                fn (Builder $query): Builder => TransactionTenantQuery::scopeCustomersForUser($query, auth()->user()),
                            )
                            ->searchable(['full_name', 'phone', 'national_id'])
                            ->preload()
                            ->required(),
                        Select::make('transaction_type')
                            ->label('Type')
                            ->options(TransactionType::options())
                            ->required()
                            ->default(TransactionType::SEND_MONEY->value),
                        Select::make('operator')
                            ->options(TransferOperator::options())
                            ->required()
                            ->default(TransferOperator::OTHER->value),
                        TextInput::make('transaction_reference')
                            ->maxLength(255),
                        TextInput::make('sender_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('receiver_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sender_country')
                            ->required()
                            ->minLength(2)
                            ->maxLength(2)
                            ->default('MA'),
                        TextInput::make('receiver_country')
                            ->required()
                            ->minLength(2)
                            ->maxLength(2),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('currency')
                            ->required()
                            ->minLength(3)
                            ->maxLength(3)
                            ->default('MAD'),
                        TextInput::make('fees')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        Select::make('status')
                            ->options(TransactionStatus::options())
                            ->required()
                            ->default(TransactionStatus::PENDING->value),
                        DateTimePicker::make('transaction_date')
                            ->seconds(false)
                            ->required()
                            ->default(now()),
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
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer.national_id')
                    ->label('National ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->formatStateUsing(fn (TransactionType | string | null $state): string => self::typeFromState($state)?->label() ?? '-'),
                TextColumn::make('operator')
                    ->formatStateUsing(fn (TransferOperator | string | null $state): string => self::operatorFromState($state)?->label() ?? '-'),
                TextColumn::make('transaction_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (string | float | int | null $state, Transaction $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('fees')
                    ->formatStateUsing(fn (string | float | int | null $state, Transaction $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn (string | float | int | null $state, Transaction $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (TransactionStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (TransactionStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('transaction_date', today())),
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])),
                Filter::make('send_money')
                    ->label('Send Money')
                    ->query(fn (Builder $query): Builder => $query->where('transaction_type', TransactionType::SEND_MONEY->value)),
                Filter::make('receive_money')
                    ->label('Receive Money')
                    ->query(fn (Builder $query): Builder => $query->where('transaction_type', TransactionType::RECEIVE_MONEY->value)),
                Filter::make('completed')
                    ->label('Completed')
                    ->query(fn (Builder $query): Builder => $query->where('status', TransactionStatus::COMPLETED->value)),
                Filter::make('pending')
                    ->label('Pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', TransactionStatus::PENDING->value)),
                Filter::make('cancelled')
                    ->label('Cancelled')
                    ->query(fn (Builder $query): Builder => $query->where('status', TransactionStatus::CANCELLED->value)),
                SelectFilter::make('operator')
                    ->options(TransferOperator::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * @return Builder<Transaction>
     */
    public static function getEloquentQuery(): Builder
    {
        return TransactionTenantQuery::transactionsForUser(auth()->user())
            ->with(['agency', 'customer', 'employee']);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    private static function typeFromState(TransactionType | string | null $state): ?TransactionType
    {
        if ($state instanceof TransactionType) {
            return $state;
        }

        return $state === null ? null : TransactionType::tryFrom($state);
    }

    private static function operatorFromState(TransferOperator | string | null $state): ?TransferOperator
    {
        if ($state instanceof TransferOperator) {
            return $state;
        }

        return $state === null ? null : TransferOperator::tryFrom($state);
    }

    private static function statusFromState(TransactionStatus | string | null $state): ?TransactionStatus
    {
        if ($state instanceof TransactionStatus) {
            return $state;
        }

        return $state === null ? null : TransactionStatus::tryFrom($state);
    }
}
