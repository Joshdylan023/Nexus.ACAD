<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instituicao_id')->constrained('instituicoes');
            $table->foreignId('area_conhecimento_id')->constrained('areas_conhecimento');
            $table->string('nome');
            $table->string('codigo_interno')->unique();
            $table->enum('nivel', ['Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação', 'Mestrado', 'Doutorado', 'Extensão', 'Livre']);
            $table->integer('duracao_padrao_semestres');
            $table->integer('prazo_maximo_semestres');
            $table->foreignId('coordenador_id')->nullable()->constrained('users');
            $table->enum('status', ['Em Planejamento', 'Ativo', 'Em Extinção', 'Extinto'])->default('Em Planejamento');
            $table->integer('vagas_anuais')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cursos'); }
};