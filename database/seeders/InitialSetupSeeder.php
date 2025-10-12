<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Setor;
use App\Models\SetorVinculo;
use App\Models\User;
use App\Models\Colaborador;
use App\Models\Perfil;
use App\Models\Permissao;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // --- ETAPA A: Criar dados institucionais mínimos ---
        $this->command->info('>>> A criar estrutura institucional mínima...');
        $grupo = GrupoEducacional::firstOrCreate(['nome' => 'Grupo Nexus Principal']);
        $mantenedora = Mantenedora::firstOrCreate(['cnpj' => '00.000.000/0001-00'], ['grupo_educacional_id' => $grupo->id, 'razao_social' => 'Nexus Educacional LTDA']);
        $instituicao = Instituicao::firstOrCreate(['cnpj' => '00.000.000/0002-00'], ['mantenedora_id' => $mantenedora->id, 'razao_social' => 'Faculdade Nexus', 'nome_fantasia' => 'Faculdade Nexus', 'tipo_organizacao_academica' => 'Faculdade', 'endereco_sede' => 'Endereço da Sede não informado']);
        $campus = Campus::firstOrCreate(['nome' => 'Campus Central', 'instituicao_id' => $instituicao->id], ['endereco_completo' => 'Endereço do Campus não informado']);
        $setor_catalogo = Setor::firstOrCreate(['nome' => 'TI Corporativo', 'tipo' => 'Corporativo']);

        // --- ETAPA B: Criar a "Unidade Organizacional" (Vínculo do Setor) ---
        $this->command->info('>>> A criar o vínculo do setor (unidade organizacional)...');
        $setor_vinculo = SetorVinculo::firstOrCreate(
            [
                'setor_id' => $setor_catalogo->id,
                'vinculavel_id' => $campus->id,
                'vinculavel_type' => 'App\\Models\\Campus'
            ],
            ['status' => 'Ativo']
        );

        // --- ETAPA C: Criar a "Pessoa" (User) ---
        $this->command->info('>>> A criar o registo da pessoa (User)...');
        $pessoa = User::firstOrCreate(['cpf' => '123.456.789-00'], [
            'name' => 'Super Admin',
            'email' => 'pessoal.admin@email.com'
        ]);

        // --- ETAPA D: Criar o "Vínculo" de Colaborador ---
        $this->command->info('>>> A criar o vínculo de Colaborador...');
        Colaborador::firstOrCreate(['matricula_funcional' => 'ADM001'], [
            'user_id' => $pessoa->id,
            'unidade_organizacional_id' => $mantenedora->id,
            'unidade_organizacional_type' => 'App\\Models\\Mantenedora',
            'unidade_lotacao_id' => $campus->id,
            'unidade_lotacao_type' => 'App\\Models\\Campus',
            'setor_vinculo_id' => $setor_vinculo->id,
            'email_funcional' => 'admin@nexus.com',
            'password' => Hash::make('password'),
            'cargo' => 'Administrador do Sistema',
            'data_admissao' => now(),
            'is_gestor' => true
        ]);

        // --- ETAPA E: Atribuir todas as permissões ---
        $this->command->info('>>> A criar o Perfil Super Admin e a sincronizar permissões...');
        $perfil = Perfil::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $permissoes = Permissao::all();
        $perfil->syncPermissions($permissoes);
        $pessoa->assignRole($perfil);

        $this->command->info('>>> Processo concluído com sucesso!');
    }
}