<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    /**
     * ⭐ TODAS AS PERMISSÕES DO SISTEMA (ORGANIZADAS POR MÓDULO)
     */
    public function run(): void
    {
        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissoes = [
            // ============================================
            // 🏢 MÓDULO: INSTITUCIONAL
            // ============================================
            'visualizar-institucional' => 'Visualizar estrutura institucional',
            'gerenciar-institucional' => 'Gerenciar estrutura institucional (completo)',
            'gerenciar-grupos' => 'Gerenciar grupos educacionais',
            'gerenciar-mantenedoras' => 'Gerenciar mantenedoras',
            'gerenciar-instituicoes' => 'Gerenciar instituições',
            'gerenciar-campi' => 'Gerenciar campus',
            'gerenciar-identidade-visual' => 'Gerenciar identidade visual',
            'gerenciar-atos-regulatorios' => 'Gerenciar atos regulatórios',

            // ============================================
            // 📚 MÓDULO: ACADÊMICO
            // ============================================
            'visualizar-academico' => 'Visualizar informações acadêmicas',
            'gerenciar-academico' => 'Gerenciar módulo acadêmico completo',
            'gerenciar-cursos' => 'Gerenciar cursos',
            'gerenciar-disciplinas' => 'Gerenciar disciplinas',
            'gerenciar-curriculos' => 'Gerenciar currículos e matrizes',
            'gerenciar-ementas' => 'Gerenciar ementas',
            'ajustar-historico-academico' => 'Ajustar histórico acadêmico',
            'deferir-requerimento-aproveitamento' => 'Deferir requerimentos de aproveitamento',

            // ============================================
            // 👥 MÓDULO: PESSOAS E ACESSOS
            // ============================================
            'visualizar-colaboradores' => 'Visualizar colaboradores',
            'gerenciar-colaboradores' => 'Gerenciar colaboradores',
            'gerenciar-acessos' => 'Gerenciar acessos e permissões',
            'gerenciar-perfis' => 'Gerenciar perfis de acesso',
            'atribuir-permissoes-avulsas' => 'Atribuir permissões avulsas',
            'visualizar-logs-auditoria' => 'Visualizar logs de auditoria',
            'gerenciar-documentacao-colaborador' => 'Gerenciar documentação de colaboradores',

            // ============================================
            // 👨‍🏫 MÓDULO: PROFESSORES
            // ============================================
            'visualizar-professores' => 'Visualizar professores',
            'gerenciar-professores' => 'Gerenciar professores',
            'acessar-modulo-professores' => 'Acessar módulo de professores',
            'gerenciar-vinculos-professores' => 'Gerenciar vínculos de professores',
            'gerenciar-formacao-professores' => 'Gerenciar formação de professores',

            // ============================================
            // 💰 MÓDULO: FINANCEIRO
            // ============================================
            'visualizar-financeiro' => 'Visualizar módulo financeiro',
            'acessar-modulo-financeiro' => 'Acessar módulo financeiro',
            'gerenciar-financeiro' => 'Gerenciar financeiro',
            'anistiar-encargos-financeiros' => 'Anistiar encargos financeiros',
            'ajustar-boletos-retroativos' => 'Ajustar boletos retroativos',

            // ============================================
            // 📊 MÓDULO: RELATÓRIOS
            // ============================================
            'visualizar-relatorios' => 'Visualizar relatórios',
            'criar-relatorios' => 'Criar relatórios customizados',
            'exportar-relatorios' => 'Exportar relatórios (Excel, PDF)',
            'gerenciar-relatorios' => 'Gerenciar relatórios salvos',

            // ============================================
            // 📥 MÓDULO: IMPORTAÇÃO
            // ============================================
            'visualizar-importacoes' => 'Visualizar histórico de importações',
            'realizar-importacoes' => 'Realizar importações em massa',
            'gerenciar-templates-importacao' => 'Gerenciar templates de importação',

            // ============================================
            // 🔔 MÓDULO: NOTIFICAÇÕES E EVENTOS
            // ============================================
            'gerenciar-eventos-sistema' => 'Gerenciar eventos do sistema',
            'gerenciar-notificacoes' => 'Gerenciar notificações',

            // ============================================
            // ⚙️ MÓDULO: CONFIGURAÇÕES
            // ============================================
            'visualizar-configuracoes' => 'Visualizar configurações',
            'gerenciar-configuracoes' => 'Gerenciar configurações do sistema',
        ];

        foreach ($permissoes as $nome => $descricao) {
            Permission::updateOrCreate(
                ['name' => $nome],
                ['guard_name' => 'web']
            );
        }

        $this->command->info('✅ ' . count($permissoes) . ' permissões criadas/atualizadas!');

        // ============================================
        // 🎯 CRIAR PERFIS PADRÃO
        // ============================================
        
        // Super Admin
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Administrador'],
            ['guard_name' => 'web']
        );
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('✅ Perfil "Super Administrador" criado!');

        // Administrador
        $admin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['guard_name' => 'web']
        );
        $admin->syncPermissions([
            'visualizar-institucional',
            'gerenciar-institucional',
            'visualizar-academico',
            'gerenciar-academico',
            'visualizar-colaboradores',
            'gerenciar-colaboradores',
            'visualizar-professores',
            'gerenciar-professores',
            'visualizar-relatorios',
            'exportar-relatorios',
            'visualizar-importacoes',
            'realizar-importacoes',
        ]);
        $this->command->info('✅ Perfil "Administrador" criado!');

        // Gestor
        $gestor = Role::firstOrCreate(
            ['name' => 'Gestor'],
            ['guard_name' => 'web']
        );
        $gestor->syncPermissions([
            'visualizar-institucional',
            'visualizar-academico',
            'visualizar-colaboradores',
            'visualizar-professores',
            'visualizar-relatorios',
            'exportar-relatorios',
        ]);
        $this->command->info('✅ Perfil "Gestor" criado!');

        // Consultor
        $consultor = Role::firstOrCreate(
            ['name' => 'Consultor'],
            ['guard_name' => 'web']
        );
        $consultor->syncPermissions([
            'visualizar-institucional',
            'visualizar-academico',
            'visualizar-relatorios',
        ]);
        $this->command->info('✅ Perfil "Consultor" criado!');
    }
}
