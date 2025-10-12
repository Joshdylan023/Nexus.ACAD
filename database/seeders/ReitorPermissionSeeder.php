<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perfil;
use App\Models\Permissao;

class ReitorPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Encontra o perfil de Reitor
        $reitor = Perfil::where('name', 'Reitor')->first();

        // Encontra a permissão para gerenciar o institucional
        $permissao = Permissao::where('name', 'gerenciar-institucional')->first();

        // Se o perfil e a permissão existirem, sincroniza a permissão
        if ($reitor && $permissao) {
            $reitor->givePermissionTo($permissao);
        }
    }
}
