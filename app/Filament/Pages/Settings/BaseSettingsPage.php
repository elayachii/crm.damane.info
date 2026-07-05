<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsAccessService;
use App\Modules\Settings\Services\SettingsRepository;
use App\Modules\Settings\Services\SettingsScope;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

abstract class BaseSettingsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.settings.page';

    abstract protected static function settingsScopeType(): string;

    /**
     * @return array<\Filament\Forms\Components\Component>
     */
    abstract protected function settingsForm(): array;

    /**
     * @return list<string>
     */
    abstract protected function settingKeys(): array;

    /**
     * @return list<string>
     */
    protected function encryptedSettingKeys(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        $access = app(SettingsAccessService::class);

        return static::settingsScopeType() === SettingsScope::GLOBAL
            ? $access->canManageGlobal(auth()->user())
            : $access->canManageAgency(auth()->user());
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Settings')
                ->form($this->settingsForm())
                ->fillForm(fn (): array => app(SettingsRepository::class)->getMany($this->settingsScope(), $this->settingKeys()))
                ->action(function (array $data): void {
                    app(SettingsRepository::class)->setMany($this->settingsScope(), $data, $this->encryptedSettingKeys());

                    Notification::make()
                        ->title('Settings saved')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function settingsScope(): string
    {
        if (static::settingsScopeType() === SettingsScope::GLOBAL) {
            return SettingsScope::global();
        }

        $user = auth()->user();

        return $user !== null ? SettingsScope::forAgencyUser($user) : SettingsScope::agency(0);
    }
}
