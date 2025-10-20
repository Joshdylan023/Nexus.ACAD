<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            // ✅ Unidade Organizacional
            $table->string('unidade_organizacional_type')->nullable()->change();
            $table->unsignedBigInteger('unidade_organizacional_id')->nullable()->change();
            
            // ✅ Unidade de Lotação
            $table->string('unidade_lotacao_type')->nullable()->change();
            $table->unsignedBigInteger('unidade_lotacao_id')->nullable()->change();
            
            // ✅ Setor Vínculo (nome correto conforme imagem)
            if (Schema::hasColumn('colaboradores', 'setor_vinculo_id')) {
                $table->unsignedBigInteger('setor_vinculo_id')->nullable()->change();
            }
            
            // ✅ Gestor Imediato (conforme imagem)
            if (Schema::hasColumn('colaboradores', 'gestor_imediato_id')) {
                $table->unsignedBigInteger('gestor_imediato_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->string('unidade_organizacional_type')->nullable(false)->change();
            $table->unsignedBigInteger('unidade_organizacional_id')->nullable(false)->change();
            
            $table->string('unidade_lotacao_type')->nullable(false)->change();
            $table->unsignedBigInteger('unidade_lotacao_id')->nullable(false)->change();
            
            if (Schema::hasColumn('colaboradores', 'setor_vinculo_id')) {
                $table->unsignedBigInteger('setor_vinculo_id')->nullable(false)->change();
            }
            
            if (Schema::hasColumn('colaboradores', 'gestor_imediato_id')) {
                $table->unsignedBigInteger('gestor_imediato_id')->nullable(false)->change();
            }
        });
    }
};
