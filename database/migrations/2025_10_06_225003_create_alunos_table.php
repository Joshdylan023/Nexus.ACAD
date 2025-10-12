<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('curriculo_id')->constrained('curriculos');
            $table->string('matricula_academica')->unique();
            $table->string('email_estudante')->unique()->nullable();
            $table->string('password');
            $table->string('matricula_legado')->nullable()->index();
            // ... (outros campos de status e ingresso)
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('alunos'); }
};