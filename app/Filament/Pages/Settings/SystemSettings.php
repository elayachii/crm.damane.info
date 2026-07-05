<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use App\Modules\Settings\Services\SystemSettingsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SystemSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'System';

    protected static ?int $navigationSort = 107;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            Toggle::make('maintenance_mode')->label('Maintenance Mode')->default(false),
            TextInput::make('maintenance_message')->label('Maintenance Message')->maxLength(255),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'maintenance_mode',
            'maintenance_message',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Action::make('clear_cache')
                ->label('Clear Cache')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(SystemSettingsService::class)->clearApplicationCache();

                    Notification::make()->title('Application cache cleared')->success()->send();
                }),
            Action::make('system_information')
                ->label('System Information')
                ->form([
                    Placeholder::make('system')
                        ->content(fn (): HtmlString => $this->informationList(app(SystemSettingsService::class)->systemInformation())),
                ])
                ->action(static function (): void {
                }),
            Action::make('environment_information')
                ->label('Environment Information')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                ->form([
                    Placeholder::make('environment')
                        ->content(fn (): HtmlString => $this->informationList(app(SystemSettingsService::class)->environmentInformation())),
                ])
                ->action(static function (): void {
                }),
        ];
    }

    /**
     * @param array<string, string> $items
     */
    private function informationList(array $items): HtmlString
    {
        $html = collect($items)
            ->map(fn (string $value, string $key): string => '<div><strong>' . e($key) . ':</strong> ' . e($value) . '</div>')
            ->implode('');

        return new HtmlString($html);
    }
}
