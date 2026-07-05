<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class AppearanceSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Appearance';

    protected static ?int $navigationSort = 106;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            Select::make('theme_mode')->label('Light/Dark Mode')->options([
                'system' => 'System',
                'light' => 'Light',
                'dark' => 'Dark',
            ])->default('system'),
            ColorPicker::make('primary_color')->label('Primary Color')->default('#2563eb'),
            Select::make('sidebar_style')->label('Sidebar Style')->options([
                'expanded' => 'Expanded',
                'collapsed' => 'Collapsed',
            ])->default('expanded'),
            Toggle::make('compact_mode')->label('Compact Mode')->default(false),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'theme_mode',
            'primary_color',
            'sidebar_style',
            'compact_mode',
        ];
    }
}
