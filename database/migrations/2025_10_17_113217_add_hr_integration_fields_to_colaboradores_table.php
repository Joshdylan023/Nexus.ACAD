<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            // ✅ Campos para integração RH (NÃO QUEBRA NADA)
            $table->foreignId('hr_integration_id')->nullable()->after('foto_perfil_sistema')
                ->constrained('hr_integrations')->nullOnDelete()
                ->comment('Integração RH que criou/atualizou este colaborador');
            
            $table->string('external_id')->nullable()->after('hr_integration_id')
                ->comment('ID do colaborador no sistema externo');
            
            $table->json('hr_metadata')->nullable()->after('external_id')
                ->comment('Dados extras do sistema RH externo');
            
            $table->timestamp('synced_at')->nullable()->after('hr_metadata')
                ->comment('Última sincronização com sistema RH externo');
            
            // Índice para buscas rápidas
            $table->index(['hr_integration_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropForeign(['hr_integration_id']);
            $table->dropIndex(['hr_integration_id', 'external_id']);
            $table->dropColumn(['hr_integration_id', 'external_id', 'hr_metadata', 'synced_at']);
        });
    }
};
