<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use Illuminate\Support\Facades\Log;

final class EmailSettingsService
{
    public function sendTestEmail(string $email): void
    {
        Log::info('SMTP test email requested.', [
            'email' => $email,
            'requested_by' => auth()->id(),
        ]);
    }
}
