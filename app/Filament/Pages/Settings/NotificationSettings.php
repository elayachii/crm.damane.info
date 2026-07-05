<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use Filament\Forms\Components\Toggle;

class NotificationSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 104;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            Toggle::make('email_notifications')->label('Email Notifications')->default(true),
            Toggle::make('browser_notifications')->label('Browser Notifications')->default(true),
            Toggle::make('ticket_notifications')->label('Ticket Notifications')->default(true),
            Toggle::make('payment_notifications')->label('Payment Notifications')->default(true),
            Toggle::make('subscription_expiry_notifications')->label('Subscription Expiry Notifications')->default(true),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'email_notifications',
            'browser_notifications',
            'ticket_notifications',
            'payment_notifications',
            'subscription_expiry_notifications',
        ];
    }
}
