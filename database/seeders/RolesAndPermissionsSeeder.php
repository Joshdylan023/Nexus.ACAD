<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar roles básicas
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gestor = Role::firstOrCreate(['name' => 'gestor', 'guard_name' => 'web']);
        $professor = Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
        $aluno = Role::firstOrCreate(['name' => 'aluno', 'guard_name' => 'web']);

        // Criar permissões do módulo institucional (se necessário)
        Permission::firstOrCreate(['name' => 'gerenciar-institucional', 'guard_name' => 'web']);
        
        // Atribuir permissão ao admin
        $admin->givePermissionTo('gerenciar-institucional');
    }
}
