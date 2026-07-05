<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

final class SystemSettingsService
{
    public function clearApplicationCache(): void
    {
        Artisan::call('optimize:clear');
    }

    /**
     * @return array<string, string>
     */
    public function systemInformation(): array
    {
        return [
            'Laravel' => app()->version(),
            'PHP' => PHP_VERSION,
            'Queue Connection' => (string) config('queue.default'),
            'Cache Store' => (string) config('cache.default'),
            'Queue Size' => (string) Queue::size(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function environmentInformation(): array
    {
        return [
            'Environment' => (string) app()->environment(),
            'Debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'URL' => (string) config('app.url'),
            'Database' => (string) config('database.default'),
            'Timezone' => (string) config('app.timezone'),
        ];
    }
}
