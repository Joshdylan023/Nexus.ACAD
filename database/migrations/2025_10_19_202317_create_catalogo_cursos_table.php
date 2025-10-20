<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_cursos', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com Grupo Educacional
            $table->foreignId('grupo_educacional_id')
                  ->constrained('grupos_educacionais')
                  ->onDelete('cascade');
            
            // Relacionamento com Área de Conhecimento
            $table->foreignId('area_conhecimento_id')
                  ->constrained('areas_conhecimento')
                  ->onDelete('restrict');
            
            // Código ÚNICO no grupo (referência principal)
            $table->string('codigo', 20)->unique()->comment('Código único do curso no grupo');
            
            // Informações básicas
            $table->string('nome', 200);
            $table->string('sigla', 10)->nullable();
            
            // Nível de Ensino
            $table->enum('nivel', [
                'Ensino Médio',
                'Técnico',
                'Graduação',
                'Pós-Graduação',
                'Especialização',
                'Mestrado',
                'Doutorado',
                'Extensão',
                'Livre'
            ])->default('Graduação');
            
            // Grau Acadêmico
            $table->enum('grau', [
                'Bacharelado',
                'Licenciatura',
                'Tecnólogo',
                'Técnico',
                'Especialista',
                'Mestre',
                'Doutor',
                'Não se aplica'
            ])->nullable();
            
            // Modalidade
            $table->enum('modalidade', ['presencial', 'ead', 'semipresencial'])->default('presencial');
            
            // Duração
            $table->integer('duracao_padrao_semestres')->nullable();
            $table->integer('prazo_maximo_semestres')->nullable();
            
            // Carga Horária
            $table->integer('carga_horaria_total')->nullable()->comment('Em horas');
            
            // Descrição
            $table->text('descricao')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('perfil_egresso')->nullable();
            
            // Status
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['grupo_educacional_id', 'codigo']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_cursos');
    }
};
