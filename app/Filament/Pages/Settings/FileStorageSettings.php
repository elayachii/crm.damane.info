<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Modules\Settings\Services\SettingsScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class FileStorageSettings extends BaseSettingsPage
{
    protected static ?string $navigationLabel = 'File Storage';

    protected static ?int $navigationSort = 108;

    protected static function settingsScopeType(): string
    {
        return SettingsScope::GLOBAL;
    }

    protected function settingsForm(): array
    {
        return [
            Select::make('storage_driver')->label('Storage Driver')->options([
                'local' => 'Local',
                's3' => 'S3',
            ])->default('local'),
            TextInput::make('s3_key')->label('S3 Key')->maxLength(255),
            TextInput::make('s3_secret')->label('S3 Secret')->password()->revealable(),
            TextInput::make('s3_region')->label('S3 Region')->maxLength(100),
            TextInput::make('s3_bucket')->label('S3 Bucket')->maxLength(255),
            TextInput::make('s3_endpoint')->label('S3 Endpoint')->maxLength(255),
        ];
    }

    protected function settingKeys(): array
    {
        return [
            'storage_driver',
            's3_key',
            's3_secret',
            's3_region',
            's3_bucket',
            's3_endpoint',
        ];
    }

    protected function encryptedSettingKeys(): array
    {
        return ['s3_secret'];
    }
}
