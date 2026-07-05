<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers;

use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\EditAction;
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

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer profile')
                    ->components([
                        Select::make('agency_id')
                            ->relationship('agency', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                        TextInput::make('full_name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('username')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                        TextInput::make('country')
                            ->required()
                            ->minLength(2)
                            ->maxLength(2)
                            ->default('MA'),
                        TextInput::make('language')
                            ->required()
                            ->maxLength(10)
                            ->default('fr'),
                        Select::make('status')
                            ->options(CustomerStatus::options())
                            ->required()
                            ->default(CustomerStatus::ACTIVE->value),
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
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('country')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (CustomerStatus | string | null $state): string => self::statusFromState($state)?->label() ?? '-')
                    ->color(fn (CustomerStatus | string | null $state): string => self::statusFromState($state)?->color() ?? 'gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CustomerStatus::options()),
                Filter::make('created_today')
                    ->label('Created Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('created_this_month')
                    ->label('Created This Month')
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
     * @return Builder<Customer>
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return parent::getEloquentQuery()->with(['agency', 'creator']);
        }

        return parent::getEloquentQuery()
            ->with(['agency', 'creator'])
            ->where('agency_id', $user?->agency_id ?? 0);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    private static function statusFromState(CustomerStatus | string | null $state): ?CustomerStatus
    {
        if ($state instanceof CustomerStatus) {
            return $state;
        }

        if ($state === null) {
            return null;
        }

        return CustomerStatus::tryFrom($state);
    }
}
