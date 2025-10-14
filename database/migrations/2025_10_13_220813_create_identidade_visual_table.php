<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identidade_visual', function (Blueprint $table) {
            $table->id();
            
            // ⭐ POLIMÓRFICO - Grupo, Mantenedora ou Instituição
            $table->morphs('entidade'); // ⭐ JÁ CRIA O ÍNDICE AUTOMATICAMENTE!
            
            // Logos (paths)
            $table->string('logo_principal')->nullable();
            $table->string('logo_horizontal')->nullable();
            $table->string('logo_icone')->nullable();
            $table->string('logo_marca_dagua')->nullable();
            
            // Cores (paleta)
            $table->string('cor_primaria')->default('#667EEA');
            $table->string('cor_secundaria')->default('#764BA2');
            $table->string('cor_acento')->default('#F59E0B');
            $table->string('cor_texto')->default('#1F2937');
            
            // Tipografia
            $table->string('fonte_principal')->default('Inter');
            $table->string('fonte_secundaria')->default('Poppins');
            
            // Configurações de documentos
            $table->boolean('usar_logo_documentos')->default(true);
            $table->boolean('usar_marca_dagua')->default(false);
            $table->enum('posicao_logo', ['topo-esquerda', 'topo-centro', 'topo-direita'])->default('topo-esquerda');
            
            // Rodapé de documentos
            $table->text('texto_rodape')->nullable();
            $table->string('site')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            
            // Metadados
            $table->text('observacoes')->nullable();
            
            // Auditoria
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // ⭐ REMOVA ESTA LINHA - morphs() já cria o índice!
            // $table->index(['entidade_type', 'entidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identidade_visual');
    }
};
