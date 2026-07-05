<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports\Concerns;

use App\Modules\Reports\Policies\ReportPolicy;

trait AuthorizesReports
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && app(ReportPolicy::class)->viewAny($user);
    }
}
