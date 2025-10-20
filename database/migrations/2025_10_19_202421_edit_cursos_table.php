<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            // ==========================================
            // ADICIONAR NOVAS COLUNAS
            // ==========================================
            
            // Referência ao catálogo (nullable inicialmente)
            $table->foreignId('catalogo_curso_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('catalogo_cursos')
                  ->onDelete('restrict')
                  ->comment('Referência ao curso no catálogo do grupo');
            
            // Campus (OPCIONAL - para cursos específicos de campus)
            $table->foreignId('campus_id')
                  ->nullable()
                  ->after('instituicao_id')
                  ->constrained('campi')
                  ->onDelete('cascade')
                  ->comment('Campus específico (opcional)');
            
            // Renomear codigo_interno para codigo_ies (via nova coluna)
            $table->string('codigo_ies', 20)
                  ->nullable()
                  ->after('nome')
                  ->comment('Código interno específico da IES');
            
            // Adicionar campos faltantes
            $table->string('sigla', 10)->nullable()->after('nome');
            
            $table->enum('grau', [
                'Bacharelado',
                'Licenciatura',
                'Tecnólogo',
                'Técnico',
                'Especialista',
                'Mestre',
                'Doutor',
                'Não se aplica'
            ])->nullable()->after('nivel');
            
            $table->enum('modalidade', ['presencial', 'ead', 'semipresencial'])
                  ->default('presencial')
                  ->after('grau');
            
            $table->integer('carga_horaria_total')
                  ->nullable()
                  ->comment('Em horas')
                  ->after('prazo_maximo_semestres');
            
            // Auditoria
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('vagas_anuais')
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->foreignId('updated_by')
                  ->nullable()
                  ->after('created_by')
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Soft deletes
            $table->softDeletes()->after('updated_at');
        });
        
        // ==========================================
        // MIGRAR DADOS EXISTENTES
        // ==========================================
        
        // Copiar codigo_interno para codigo_ies
        DB::statement('UPDATE cursos SET codigo_ies = codigo_interno WHERE codigo_interno IS NOT NULL');
        
        // ==========================================
        // REMOVER COLUNA ANTIGA
        // ==========================================
        
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('codigo_interno');
        });
        
        // ==========================================
        // ADICIONAR ÍNDICES
        // ==========================================
        
        Schema::table('cursos', function (Blueprint $table) {
            // Índice único: catalogo_curso + instituicao + campus
            // Um curso do catálogo só pode aparecer 1x por instituição/campus
            $table->unique(
                ['catalogo_curso_id', 'instituicao_id', 'campus_id'], 
                'unique_curso_inst_campus'
            );
            
            $table->index('codigo_ies');
            $table->index(['instituicao_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            // Remover índices
            $table->dropUnique('unique_curso_inst_campus');
            $table->dropIndex(['codigo_ies']);
            $table->dropIndex(['instituicao_id', 'status']);
            
            // Restaurar codigo_interno
            $table->string('codigo_interno', 50)->nullable();
            
            // Copiar codigo_ies de volta para codigo_interno
            DB::statement('UPDATE cursos SET codigo_interno = codigo_ies WHERE codigo_ies IS NOT NULL');
            
            // Remover novas colunas
            $table->dropSoftDeletes();
            
            if (Schema::hasColumn('cursos', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('cursos', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            if (Schema::hasColumn('cursos', 'campus_id')) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            }
            
            if (Schema::hasColumn('cursos', 'catalogo_curso_id')) {
                $table->dropForeign(['catalogo_curso_id']);
                $table->dropColumn('catalogo_curso_id');
            }
            
            $table->dropColumn([
                'codigo_ies',
                'sigla',
                'grau',
                'modalidade',
                'carga_horaria_total'
            ]);
        });
    }
};
