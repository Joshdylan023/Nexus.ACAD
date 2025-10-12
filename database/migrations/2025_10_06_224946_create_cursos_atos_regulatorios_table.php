<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cursos_atos_regulatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->string('codigo_mec');
            $table->string('codigo_emec')->nullable();
            $table->enum('tipo_ato', ['Autorização', 'Reconhecimento', 'Renovação de Reconhecimento']);
            $table->string('numero_portaria');
            $table->date('data_publicacao_dou');
            $table->string('link_publicacao')->nullable();
            $table->date('data_validade_ato')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cursos_atos_regulatorios'); }
};