<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class SecuritySettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Security';

    protected static ?int $navigationSort = 105;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            Select::make('password_policy')->label('Password Policy')->options([
                'standard' => 'Standard',
                'strong' => 'Strong',
                'strict' => 'Strict',
            ])->default('strong'),
            TextInput::make('session_timeout')->label('Session Timeout (minutes)')->numeric()->default(120),
            Toggle::make('two_factor_enabled')->label('Two-Factor Authentication')->default(false),
            TextInput::make('login_attempts_limit')->label('Login Attempts Limit')->numeric()->default(5),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'password_policy',
            'session_timeout',
            'two_factor_enabled',
            'login_attempts_limit',
        ];
    }
}
