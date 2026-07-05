<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Authorization\PermissionRegistry;
use App\Support\Authorization\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionRegistry::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (RoleName::all() as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName(RoleName::SUPER_ADMIN, 'web')
            ->syncPermissions(PermissionRegistry::all());

        Role::findByName(RoleName::AGENCY_OWNER, 'web')
            ->syncPermissions(PermissionRegistry::all());

        Role::findByName(RoleName::MANAGER, 'web')
            ->syncPermissions(array_values(array_diff(
                PermissionRegistry::all(),
                PermissionRegistry::forResources(['agencies']),
            )));

        Role::findByName(RoleName::EMPLOYEE, 'web')
            ->syncPermissions(PermissionRegistry::forResources([
                'customers',
                'subscriptions',
                'devices',
            ]));

        Role::findByName(RoleName::VIEWER, 'web')
            ->syncPermissions(PermissionRegistry::forResources(
                PermissionRegistry::resources(),
                [
                    PermissionRegistry::ACTION_VIEW,
                    PermissionRegistry::ACTION_VIEW_ANY,
                ],
            ));

        User::query()
            ->whereDoesntHave('roles')
            ->whereNotNull('agency_id')
            ->orderBy('id')
            ->get()
            ->groupBy('agency_id')
            ->each(static function ($users): void {
                $users->first()?->assignRole(RoleName::AGENCY_OWNER);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
