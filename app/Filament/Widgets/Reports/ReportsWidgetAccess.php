<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Reports;

use App\Modules\Reports\Policies\ReportPolicy;

trait ReportsWidgetAccess
{
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null && app(ReportPolicy::class)->viewAny($user);
    }
}
