<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaborador_instituicao_acesso', function (Blueprint $table) {
            // Adicionar colunas para roles e permissões específicas por instituição
            $table->json('roles')->nullable()->after('instituicao_id');
            $table->json('permissions')->nullable()->after('roles');
        });
    }

    public function down(): void
    {
        Schema::table('colaborador_instituicao_acesso', function (Blueprint $table) {
            $table->dropColumn(['roles', 'permissions']);
        });
    }
};
