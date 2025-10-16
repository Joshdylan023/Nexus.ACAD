<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar coluna descricao na tabela perfis
        Schema::table('perfis', function (Blueprint $table) {
            $table->text('descricao')->nullable()->after('guard_name');
        });

        // Adicionar coluna descricao na tabela permissoes
        Schema::table('permissoes', function (Blueprint $table) {
            $table->text('descricao')->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfis', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });

        Schema::table('permissoes', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });
    }
};
