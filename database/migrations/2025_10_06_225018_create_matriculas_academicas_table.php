<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas_academicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->integer('ano');
            $table->integer('semestre');
            $table->enum('status', ['Cursando', 'Aprovado', 'Reprovado por Nota', 'Reprovado por Frequência', 'Cancelado', 'Dispensado']);
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->integer('frequencia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas_academicas');
    }
};