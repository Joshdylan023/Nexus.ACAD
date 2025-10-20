<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🚀 Iniciando seeders do sistema...');
        $this->command->info('');

        // ============================================
        // 1️⃣ PERMISSÕES E PERFIS (PRIMEIRO)
        // ============================================
        $this->command->info('📋 Executando seeder de Permissões...');
        $this->call(PermissaoSeeder::class);
        
        $this->command->info('');
        $this->command->info('🏗️ Executando seeder de Espaços Físicos...');
        $this->call(EspacoFisicoPermissionsSeeder::class);

        // ============================================
        // 2️⃣ CONFIGURAÇÃO INICIAL (DEPOIS)
        // ============================================
        $this->command->info('');
        $this->command->info('⚙️ Executando seeder de Configuração Inicial...');
        $this->call(InitialSetupSeeder::class);

        // ============================================
        // 🎉 FINALIZADO
        // ============================================
        $this->command->info('');
        $this->command->info('✅ Todos os seeders foram executados com sucesso!');
        $this->command->info('');
        $this->command->info('📊 Resumo:');
        $this->command->info('   ✅ Permissões e Perfis criados');
        $this->command->info('   ✅ Permissões de Espaços Físicos criadas');
        $this->command->info('   ✅ Configuração inicial aplicada');
        $this->command->info('');
    }
}
