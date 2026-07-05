<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Models\User;

final class SettingsScope
{
    public const GLOBAL = 'global';

    public static function global(): string
    {
        return self::GLOBAL;
    }

    public static function agency(int $agencyId): string
    {
        return 'agency:' . $agencyId;
    }

    public static function forAgencyUser(User $user): string
    {
        return self::agency((int) $user->agency_id);
    }
}
