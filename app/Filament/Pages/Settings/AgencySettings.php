<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class AgencySettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Agency';

    protected static ?int $navigationSort = 102;

    protected static function settingsScopeType(): string
    {
        return 'agency';
    }

    protected function settingsForm(): array
    {
        return [
            TextInput::make('agency_profile_name')->label('Agency Name')->required()->maxLength(255),
            Textarea::make('agency_profile_address')->label('Agency Address')->columnSpanFull()->rows(3),
            FileUpload::make('branding_logo')->label('Branding Logo')->image()->directory('settings/agency-branding'),
            TextInput::make('branding_primary_color')->label('Branding Primary Color')->maxLength(20)->default('#2563eb'),
            TextInput::make('default_currency')->maxLength(3)->default('MAD'),
            Select::make('default_language')->options(['en' => 'English', 'fr' => 'French', 'ar' => 'Arabic'])->default('fr'),
            Toggle::make('is_active')->label('Active Status')->default(true),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'agency_profile_name',
            'agency_profile_address',
            'branding_logo',
            'branding_primary_color',
            'default_currency',
            'default_language',
            'is_active',
        ];
    }
}
