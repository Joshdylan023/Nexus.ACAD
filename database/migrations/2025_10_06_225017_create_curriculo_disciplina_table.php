<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculo_disciplina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculo_id')->constrained('curriculos')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->integer('periodo_sugerido');
            $table->enum('tipo_disciplina', ['Obrigatória', 'Eletiva']);
            $table->string('pre_requisitos')->nullable();
            $table->timestamps();
            $table->unique(['curriculo_id', 'disciplina_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculo_disciplina');
    }
};