<?php

declare(strict_types=1);

namespace App\Support\Authorization;

final class RoleName
{
    public const SUPER_ADMIN = 'Super Admin';
    public const AGENCY_OWNER = 'Agency Owner';
    public const MANAGER = 'Manager';
    public const EMPLOYEE = 'Employee';
    public const VIEWER = 'Viewer';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::AGENCY_OWNER,
            self::MANAGER,
            self::EMPLOYEE,
            self::VIEWER,
        ];
    }
}
