<?php

declare(strict_types=1);

namespace App\Support\Authorization;

final class PermissionRegistry
{
    public const ACTION_VIEW = 'view';
    public const ACTION_VIEW_ANY = 'view_any';
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_DELETE_ANY = 'delete_any';

    /**
     * @return list<string>
     */
    public static function actions(): array
    {
        return [
            self::ACTION_VIEW,
            self::ACTION_VIEW_ANY,
            self::ACTION_CREATE,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_DELETE_ANY,
        ];
    }

    /**
     * @return list<string>
     */
    public static function resources(): array
    {
        return [
            'agencies',
            'users',
            'customers',
            'applications',
            'devices',
            'subscriptions',
            'orders',
            'payments',
            'invoices',
            'tickets',
            'reports',
            'settings',
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::resources() as $resource) {
            foreach (self::actions() as $action) {
                $permissions[] = self::for($action, $resource);
            }
        }

        return $permissions;
    }

    public static function for(string $action, string $resource): string
    {
        return "{$action} {$resource}";
    }

    /**
     * @param list<string> $resources
     * @return list<string>
     */
    public static function forResources(array $resources, ?array $actions = null): array
    {
        $actions ??= self::actions();
        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = self::for($action, $resource);
            }
        }

        return $permissions;
    }
}
