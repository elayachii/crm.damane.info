<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Models\BackupRecord;
use App\Modules\Settings\Services\BackupService;
use App\Modules\Settings\Services\SettingsScope;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class BackupSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'Backup';

    protected static ?int $navigationSort = 109;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [];
    }

    protected function settingKeys(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manual_backup')
                ->label('Manual Backup')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(BackupService::class)->createManualBackup(auth()->user());

                    Notification::make()->title('Manual backup queued')->success()->send();
                }),
            Action::make('backup_history')
                ->label('Backup History')
                ->form([
                    Placeholder::make('history')
                        ->content(fn (): HtmlString => $this->historyList(app(BackupService::class)->history())),
                ])
                ->action(static function (): void {
                }),
            Action::make('restore')
                ->label('Restore')
                ->disabled()
                ->tooltip('Restore workflow will be enabled after backup storage is automated.'),
        ];
    }

    /**
     * @param list<BackupRecord> $records
     */
    private function historyList(array $records): HtmlString
    {
        if ($records === []) {
            return new HtmlString('<div>No backups have been recorded yet.</div>');
        }

        $html = collect($records)
            ->map(fn (BackupRecord $record): string => '<div><strong>' . e($record->filename) . '</strong> - ' . e($record->status) . ' - ' . e($record->created_at?->toDateTimeString() ?? '-') . '</div>')
            ->implode('');

        return new HtmlString($html);
    }
}
