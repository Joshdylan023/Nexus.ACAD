<?php

namespace Database\Seeders;

use App\Models\Permissao;
use Illuminate\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissoes = [
            ['name' => 'gerenciar-institucional'],
            ['name' => 'gerenciar-acessos'],
            ['name' => 'acessar-modulo-professores'],
            ['name' => 'gerenciar-documentacao-colaborador'],
            ['name' => 'ajustar-historico-academico'],
            ['name' => 'deferir-requerimento-aproveitamento'],
            ['name' => 'acessar-modulo-financeiro'],
            ['name' => 'anistiar-encargos-financeiros'],
            ['name' => 'ajustar-boletos-retroativos'],
            ['name' => 'gerenciar-academico'],
            ['name' => 'gerenciar-professores'],

        ];

        foreach ($permissoes as $permissao) {
            Permissao::updateOrCreate(['name' => $permissao['name']]);
        }
    }
}
