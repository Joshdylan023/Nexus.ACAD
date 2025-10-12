<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenedora_id')->constrained('mantenedoras');
            $table->string('razao_social');
            $table->string('nome_fantasia');
            $table->string('cnpj')->unique();
            $table->enum('tipo_organizacao_academica', ['Faculdade', 'Centro Universitário', 'Universidade']);
            $table->foreignId('reitor_id')->nullable()->constrained('users');
            $table->text('endereco_sede'); // <-- Coluna que faltava
            $table->enum('status', ['Ativo', 'Inativo', 'Em Extinção'])->default('Ativo');
            $table->string('codigo_sap')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituicoes');
    }
};