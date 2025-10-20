<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_sync_logs', function (Blueprint $table) { // ✅ CORRIGIDO
            $table->id();
            
            // Relacionamento
            $table->foreignId('integration_id')->constrained('hr_integrations')->onDelete('cascade'); // ✅ CORRIGIDO
            
            // Informações da sincronização
            $table->enum('type', ['colaboradores', 'estrutura', 'cargos', 'salarios', 'completo']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial']);
            
            // Métricas
            $table->integer('records_total')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_failed')->default(0);
            $table->integer('records_skipped')->default(0);
            
            // Tempo de execução
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            
            // Detalhes
            $table->text('message')->nullable();
            $table->json('errors')->nullable(); // Array de erros
            $table->json('summary')->nullable(); // Resumo detalhado
            
            // Metadata
            $table->foreignId('triggered_by')->nullable()->constrained('users');
            $table->string('trigger_type')->default('manual'); // manual, scheduled, webhook
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_sync_logs'); // ✅ CORRIGIDO
    }
};
