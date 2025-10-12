<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ementas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->integer('versao')->default(1);
            $table->text('conteudo_detalhado');
            $table->date('data_inicio_vigencia');
            $table->date('data_fim_vigencia')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ementas'); }
};