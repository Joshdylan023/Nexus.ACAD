<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituicao_atos_regulatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instituicao_id')->constrained('instituicoes')->onDelete('cascade');
            $table->enum('tipo_ato', ['Credenciamento', 'Recredenciamento', 'Outro']);
            $table->string('numero_portaria');
            $table->date('data_publicacao_dou');
            $table->string('link_publicacao')->nullable();
            $table->date('data_validade_ato')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituicao_atos_regulatorios');
    }
};