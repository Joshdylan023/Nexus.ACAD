<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            // Adiciona sigla
            if (!Schema::hasColumn('instituicoes', 'sigla')) {
                $table->string('sigla', 20)->nullable()->after('nome_fantasia');
            }
            
            // Adiciona categoria_administrativa
            if (!Schema::hasColumn('instituicoes', 'categoria_administrativa')) {
                $table->string('categoria_administrativa', 100)->nullable()->after('tipo_organizacao_academica');
            }
            
            // Adiciona codigo_mec
            if (!Schema::hasColumn('instituicoes', 'codigo_mec')) {
                $table->string('codigo_mec', 20)->nullable()->after('categoria_administrativa');
            }
            
            // Adiciona logradouro
            if (!Schema::hasColumn('instituicoes', 'logradouro')) {
                $table->string('logradouro')->nullable()->after('endereco_sede');
            }
            
            // Adiciona numero
            if (!Schema::hasColumn('instituicoes', 'numero')) {
                $table->string('numero', 20)->nullable()->after('logradouro');
            }
            
            // Adiciona complemento
            if (!Schema::hasColumn('instituicoes', 'complemento')) {
                $table->string('complemento')->nullable()->after('numero');
            }
            
            // Adiciona bairro
            if (!Schema::hasColumn('instituicoes', 'bairro')) {
                $table->string('bairro')->nullable()->after('complemento');
            }
            
            // Adiciona cidade
            if (!Schema::hasColumn('instituicoes', 'cidade')) {
                $table->string('cidade')->nullable()->after('bairro');
            }
            
            // Adiciona estado
            if (!Schema::hasColumn('instituicoes', 'estado')) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }
            
            // Adiciona cep
            if (!Schema::hasColumn('instituicoes', 'cep')) {
                $table->string('cep', 10)->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $columns = [
                'sigla', 
                'codigo_mec', 
                'categoria_administrativa', 
                'logradouro', 
                'numero', 
                'complemento', 
                'bairro', 
                'cidade', 
                'estado', 
                'cep'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('instituicoes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
