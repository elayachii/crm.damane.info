<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

final class SettingsRepository
{
    /**
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    public function getMany(string $scope, array $keys): array
    {
        $settings = Setting::query()
            ->where('scope', $scope)
            ->whereIn('key', $keys)
            ->get()
            ->keyBy('key');

        $values = [];

        foreach ($keys as $key) {
            $setting = $settings->get($key);
            $values[$key] = $setting instanceof Setting ? $this->decode($setting) : null;
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $values
     * @param list<string> $encryptedKeys
     */
    public function setMany(string $scope, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            $isEncrypted = in_array($key, $encryptedKeys, true);

            if ($isEncrypted && blank($value)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['scope' => $scope, 'key' => $key],
                [
                    'value' => $this->encode($value, $isEncrypted),
                    'type' => $this->typeFor($value),
                    'is_encrypted' => $isEncrypted,
                ],
            );
        }
    }

    private function decode(Setting $setting): mixed
    {
        $value = $setting->is_encrypted && $setting->value !== null
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'array' => json_decode((string) $value, true),
            default => $value,
        };
    }

    private function encode(mixed $value, bool $encrypted): ?string
    {
        if ($value === null) {
            return null;
        }

        $encoded = is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : (string) $value;

        return $encrypted ? Crypt::encryptString($encoded) : $encoded;
    }

    private function typeFor(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}
