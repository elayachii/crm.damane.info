<?php

declare(strict_types=1);

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;

return [
    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => false,
        'cluster' => null,
        'tabs' => [
            'pages' => false,
            'widgets' => false,
            'resources' => true,
            'custom_permissions' => true,
        ],
    ],

    'tenant_model' => null,
    'auth_provider_model' => App\Models\User::class,

    'super_admin' => [
        'enabled' => true,
        'name' => 'Super Admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'Agency Owner',
    ],

    'permissions' => [
        'separator' => ' ',
        'case' => 'snake',
        'generate' => true,
    ],

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'deleteAny',
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
        ],
    ],

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield.resource_permission_prefixes_labels',
    ],

    'resources' => [
        'subject' => 'model',
        'manage' => [
            RoleResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'deleteAny',
            ],
        ],
        'exclude' => [],
    ],

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            Dashboard::class,
        ],
    ],

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            AccountWidget::class,
        ],
    ],

    'custom_permissions' => [],

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    'register_role_policy' => true,
];
