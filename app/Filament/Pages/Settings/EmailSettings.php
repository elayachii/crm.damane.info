<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\EmailSettingsService;
use App\Modules\Settings\Services\SettingsScope;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class EmailSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Email';

    protected static ?int $navigationSort = 103;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            TextInput::make('smtp_host')->label('SMTP Host')->maxLength(255),
            TextInput::make('smtp_port')->label('SMTP Port')->numeric()->default(587),
            TextInput::make('smtp_username')->label('Username')->maxLength(255),
            TextInput::make('smtp_password')->label('Password')->password()->revealable(),
            Select::make('smtp_encryption')->label('Encryption')->options(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'])->default('tls'),
            TextInput::make('mail_from_name')->label('From Name')->maxLength(255),
            TextInput::make('mail_from_email')->label('From Email')->email()->maxLength(255),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'mail_from_name',
            'mail_from_email',
        ];
    }

    protected function encryptedSettingKeys(): array
    {
        return ['smtp_password'];
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Action::make('send_test_email')
                ->label('Send Test Email')
                ->form([
                    TextInput::make('email')->email()->required(),
                ])
                ->action(function (array $data): void {
                    app(EmailSettingsService::class)->sendTestEmail($data['email']);

                    Notification::make()->title('Test email request logged')->success()->send();
                }),
        ];
    }
}
