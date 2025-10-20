<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EspacoFisicoPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ============================================
            // 🏗️ MÓDULO GERAL - ESPAÇOS FÍSICOS
            // ============================================
            'gerenciar-espacos-fisicos' => 'Gerenciar módulo completo de espaços físicos',
            'visualizar-dashboard-espacos' => 'Visualizar dashboard 360° de espaços físicos',
            
            // ============================================
            // 🏢 PRÉDIOS
            // ============================================
            'visualizar-predios' => 'Visualizar prédios',
            'criar-predios' => 'Criar novos prédios',
            'editar-predios' => 'Editar prédios existentes',
            'excluir-predios' => 'Excluir prédios',
            
            // ============================================
            // 🧱 BLOCOS
            // ============================================
            'visualizar-blocos' => 'Visualizar blocos',
            'criar-blocos' => 'Criar novos blocos',
            'editar-blocos' => 'Editar blocos existentes',
            'excluir-blocos' => 'Excluir blocos',
            
            // ============================================
            // 📐 ANDARES
            // ============================================
            'visualizar-andares' => 'Visualizar andares',
            'criar-andares' => 'Criar novos andares',
            'editar-andares' => 'Editar andares existentes',
            'excluir-andares' => 'Excluir andares',
            
            // ============================================
            // 🚪 ESPAÇOS FÍSICOS (SALAS)
            // ============================================
            'visualizar-espacos' => 'Visualizar espaços físicos',
            'criar-espacos' => 'Criar novos espaços físicos',
            'editar-espacos' => 'Editar espaços físicos existentes',
            'excluir-espacos' => 'Excluir espaços físicos',
            'gerenciar-recursos-espacos' => 'Gerenciar recursos dos espaços (equipamentos)',
            
            // ============================================
            // 📅 RESERVAS DE ESPAÇOS
            // ============================================
            'visualizar-reservas' => 'Visualizar reservas de espaços',
            'criar-reservas' => 'Criar reservas de espaços',
            'editar-reservas' => 'Editar minhas reservas',
            'cancelar-reservas' => 'Cancelar minhas reservas',
            'aprovar-reservas' => 'Aprovar ou rejeitar reservas',
            'gerenciar-todas-reservas' => 'Gerenciar todas as reservas do sistema',
            'visualizar-calendario-reservas' => 'Visualizar calendário de reservas',
            'exportar-relatorio-reservas' => 'Exportar relatórios de reservas',
        ];

        // Criar permissões
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        $this->command->info('✅ ' . count($permissions) . ' permissões de Espaços Físicos criadas!');

        // ============================================
        // 🎯 ATRIBUIR PERMISSÕES AOS PERFIS
        // ============================================

        // Super Administrador - Tudo
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador']);
        $superAdmin->givePermissionTo(array_keys($permissions));
        $this->command->info('✅ Permissões atribuídas ao perfil "Super Administrador"');

        // Administrador - Gestão completa
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->givePermissionTo([
            'gerenciar-espacos-fisicos',
            'visualizar-dashboard-espacos',
            'visualizar-predios',
            'criar-predios',
            'editar-predios',
            'excluir-predios',
            'visualizar-blocos',
            'criar-blocos',
            'editar-blocos',
            'excluir-blocos',
            'visualizar-andares',
            'criar-andares',
            'editar-andares',
            'excluir-andares',
            'visualizar-espacos',
            'criar-espacos',
            'editar-espacos',
            'excluir-espacos',
            'gerenciar-recursos-espacos',
            'visualizar-reservas',
            'criar-reservas',
            'editar-reservas',
            'cancelar-reservas',
            'aprovar-reservas',
            'gerenciar-todas-reservas',
            'visualizar-calendario-reservas',
            'exportar-relatorio-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Administrador"');

        // Gestor de Infraestrutura - Gestão de espaços sem reservas
        $gestorInfra = Role::firstOrCreate(['name' => 'Gestor de Infraestrutura']);
        $gestorInfra->givePermissionTo([
            'visualizar-dashboard-espacos',
            'visualizar-predios',
            'criar-predios',
            'editar-predios',
            'visualizar-blocos',
            'criar-blocos',
            'editar-blocos',
            'visualizar-andares',
            'criar-andares',
            'editar-andares',
            'visualizar-espacos',
            'criar-espacos',
            'editar-espacos',
            'gerenciar-recursos-espacos',
            'visualizar-reservas',
            'visualizar-calendario-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Gestor de Infraestrutura"');

        // Gestor de Reservas - Aprovação de reservas
        $gestorReservas = Role::firstOrCreate(['name' => 'Gestor de Reservas']);
        $gestorReservas->givePermissionTo([
            'visualizar-espacos',
            'visualizar-reservas',
            'criar-reservas',
            'editar-reservas',
            'cancelar-reservas',
            'aprovar-reservas',
            'gerenciar-todas-reservas',
            'visualizar-calendario-reservas',
            'exportar-relatorio-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Gestor de Reservas"');

        // Gestor Acadêmico - Visualizar e criar reservas
        $gestorAcademico = Role::firstOrCreate(['name' => 'Gestor Acadêmico']);
        $gestorAcademico->givePermissionTo([
            'visualizar-espacos',
            'visualizar-reservas',
            'criar-reservas',
            'editar-reservas',
            'cancelar-reservas',
            'visualizar-calendario-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Gestor Acadêmico"');

        // Professor - Apenas criar e gerenciar suas reservas
        $professor = Role::firstOrCreate(['name' => 'Professor']);
        $professor->givePermissionTo([
            'visualizar-espacos',
            'visualizar-reservas',
            'criar-reservas',
            'editar-reservas',
            'cancelar-reservas',
            'visualizar-calendario-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Professor"');

        // Colaborador - Apenas visualizar e criar reservas básicas
        $colaborador = Role::firstOrCreate(['name' => 'Colaborador']);
        $colaborador->givePermissionTo([
            'visualizar-espacos',
            'visualizar-reservas',
            'criar-reservas',
            'editar-reservas',
            'cancelar-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Colaborador"');

        // Consultor - Apenas visualização
        $consultor = Role::firstOrCreate(['name' => 'Consultor']);
        $consultor->givePermissionTo([
            'visualizar-espacos',
            'visualizar-reservas',
            'visualizar-calendario-reservas',
        ]);
        $this->command->info('✅ Permissões atribuídas ao perfil "Consultor"');

        $this->command->info('');
        $this->command->info('🎉 Seeder de Espaços Físicos executado com sucesso!');
    }
}
