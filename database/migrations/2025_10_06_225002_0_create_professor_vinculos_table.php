<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('professor_vinculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instituicao_id')->constrained('instituicoes')->onDelete('cascade');
            $table->string('matricula_funcional');
            $table->string('email_funcional')->unique()->nullable();
            $table->string('password');
            $table->enum('status', ['Ativo', 'Afastado', 'Desligado'])->default('Ativo');
            $table->timestamps();
            $table->unique(['instituicao_id', 'matricula_funcional']);
        });
    }
    public function down(): void { Schema::dropIfExists('professor_vinculos'); }
};