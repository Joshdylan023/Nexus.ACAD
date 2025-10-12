<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos');
            $table->string('nome_matriz');
            $table->enum('tipo_matriz', ['Fechada', 'Aberta']);
            $table->string('codigo_curriculo');
            $table->date('data_inicio_vigencia');
            $table->date('data_fim_vigencia')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('curriculos'); }
};