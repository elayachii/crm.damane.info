<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class GeneralSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'General';

    protected static ?int $navigationSort = 101;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            TextInput::make('company_name')->label('Company Name')->required()->maxLength(255),
            FileUpload::make('company_logo')->label('Company Logo')->image()->directory('settings/logos'),
            TextInput::make('company_email')->label('Company Email')->email()->maxLength(255),
            TextInput::make('company_phone')->label('Company Phone')->tel()->maxLength(50),
            Textarea::make('address')->columnSpanFull()->rows(3),
            Select::make('timezone')->options($this->timezones())->searchable()->default('Africa/Casablanca'),
            TextInput::make('currency')->maxLength(3)->default('MAD'),
            Select::make('language')->options(['en' => 'English', 'fr' => 'French', 'ar' => 'Arabic'])->default('fr'),
            Select::make('date_format')->label('Date Format')->options([
                'Y-m-d' => 'YYYY-MM-DD',
                'd/m/Y' => 'DD/MM/YYYY',
                'm/d/Y' => 'MM/DD/YYYY',
            ])->default('Y-m-d'),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'company_name',
            'company_logo',
            'company_email',
            'company_phone',
            'address',
            'timezone',
            'currency',
            'language',
            'date_format',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function timezones(): array
    {
        return collect(timezone_identifiers_list())
            ->mapWithKeys(fn (string $timezone): array => [$timezone => $timezone])
            ->all();
    }
}
