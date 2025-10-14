<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Grupos Educacionais
        Schema::table('grupos_educacionais', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes(); // Exclusão lógica (deleted_at)
        });

        // Mantenedoras
        Schema::table('mantenedoras', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // Instituições
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // Campus
        Schema::table('campi', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // Setores
        Schema::table('setores', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // Atos Regulatórios de Instituições
        Schema::table('instituicao_atos_regulatorios', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // Cursos Atos Regulatórios
        Schema::table('cursos_atos_regulatorios', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('grupos_educacionais', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('mantenedoras', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('instituicoes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('campi', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('setores', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('instituicao_atos_regulatorios', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });

        Schema::table('cursos_atos_regulatorios', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_at']);
        });
    }
};
