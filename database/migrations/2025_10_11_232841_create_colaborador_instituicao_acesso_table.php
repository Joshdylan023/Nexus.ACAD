<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('colaborador_instituicao_acesso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores')->onDelete('cascade');
            $table->foreignId('instituicao_id')->constrained('instituicoes')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['colaborador_id', 'instituicao_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('colaborador_instituicao_acesso'); }
};