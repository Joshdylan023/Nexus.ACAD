<?php

return [
    'models' => [
        'permission' => App\Models\Permissao::class,
        'role' => App\Models\Perfil::class,
    ],
    'table_names' => [
        'roles' => 'perfis',
        'permissions' => 'permissoes',
        'role_has_permissions' => 'perfil_permissao',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
    ],
    // ... outras configurações ...
];
