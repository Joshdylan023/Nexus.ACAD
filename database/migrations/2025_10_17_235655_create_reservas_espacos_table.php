<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas_espacos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espaco_fisico_id')->constrained('espacos_fisicos')->onDelete('cascade');
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            
            // Período
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->boolean('recorrente')->default(false);
            $table->json('dias_semana')->nullable(); // ["seg", "qua", "sex"]
            
            // Detalhes
            $table->string('motivo');
            $table->text('descricao')->nullable();
            $table->integer('quantidade_pessoas')->nullable();
            $table->json('recursos_adicionais')->nullable(); // ["projetor", "microfone"]
            
            // Status
            $table->enum('status', ['Pendente', 'Aprovada', 'Rejeitada', 'Cancelada', 'Concluída'])->default('Pendente');
            $table->text('observacoes')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['espaco_fisico_id', 'data_inicio', 'data_fim']);
            $table->index('solicitante_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_espacos');
    }
};
