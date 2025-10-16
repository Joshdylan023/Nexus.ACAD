<?php

return [
    'models' => [
        'permission' => App\Models\Permissao::class,
        'role' => App\Models\Perfil::class,
    ],

    'table_names' => [
        'roles' => 'perfis',
        'permissions' => 'permissoes',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'perfil_permissao',
    ],

    'column_names' => [
        'role_pivot_key' => null,      // ⭐ null = usa 'role_id' (padrão)
        'permission_pivot_key' => null, // ⭐ null = usa 'permission_id' (padrão)
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'teams' => false,
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
