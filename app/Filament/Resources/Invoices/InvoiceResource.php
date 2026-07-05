<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\Invoice;
use App\Modules\Invoices\Services\InvoiceTenantQuery;
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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoices';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice details')
                    ->components([
                        TextInput::make('invoice_number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('subscription_id')
                            ->label('Subscription')
                            ->relationship(
                                'subscription',
                                'plan_name',
                                fn (Builder $query): Builder => InvoiceTenantQuery::scopeSubscriptionsForUser($query, auth()->user())
                                    ->with('customer'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->customer?->full_name} - {$record->plan_name}")
                            ->searchable(['plan_name'])
                            ->preload()
                            ->required(),
                        DatePicker::make('issue_date')
                            ->required()
                            ->default(today()),
                        DatePicker::make('due_date')
                            ->required()
                            ->afterOrEqual('issue_date'),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('tax')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('discount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('currency')
                            ->required()
                            ->minLength(3)
                            ->maxLength(3)
                            ->default('MAD'),
                        Select::make('status')
                            ->options(InvoiceStatus::options())
                            ->required()
                            ->default(InvoiceStatus::DRAFT->value),
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
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn (string | float | int | null $state, Invoice $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('balance_due')
                    ->label('Balance Due')
                    ->formatStateUsing(fn (string | float | int | null $state, Invoice $record): string => number_format((float) $state, 2) . ' ' . $record->currency)
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (InvoiceStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (InvoiceStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
            ])
            ->filters([
                Filter::make('draft')
                    ->label('Draft')
                    ->query(fn (Builder $query): Builder => $query->where('status', InvoiceStatus::DRAFT->value)),
                Filter::make('paid')
                    ->label('Paid')
                    ->query(fn (Builder $query): Builder => $query->where('status', InvoiceStatus::PAID->value)),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query->where('status', InvoiceStatus::OVERDUE->value)),
                Filter::make('current_month')
                    ->label('Current Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('issue_date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ])),
                Filter::make('current_year')
                    ->label('Current Year')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('issue_date', [
                            now()->startOfYear()->toDateString(),
                            now()->endOfYear()->toDateString(),
                        ])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * @return Builder<Invoice>
     */
    public static function getEloquentQuery(): Builder
    {
        return InvoiceTenantQuery::invoicesForUser(auth()->user())
            ->with(['agency', 'customer', 'subscription']);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    private static function statusFromState(InvoiceStatus | string | null $state): ?InvoiceStatus
    {
        if ($state instanceof InvoiceStatus) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return InvoiceStatus::tryFrom($state);
    }
}
