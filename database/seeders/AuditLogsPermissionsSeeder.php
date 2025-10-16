<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditLogsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Criar permissões
        $permissions = [
            'visualizar-logs',
            'gerenciar-sistema',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Atribuir permissões ao role de Administrador (se existir)
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        $this->command->info('✅ Permissões de Logs de Auditoria criadas com sucesso!');
    }
}
