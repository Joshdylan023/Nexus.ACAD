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
        // Primeiro, executa o seeder de permissões para garantir que elas existam.
        $this->call(PermissaoSeeder::class);

        // Em seguida, executa o seeder de configuração inicial.
        $this->call(InitialSetupSeeder::class);
    }
}
