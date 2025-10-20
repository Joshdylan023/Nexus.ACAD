<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordenadores_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('colaborador_id')->constrained('colaboradores')->onDelete('cascade');
            $table->enum('tipo', ['Titular', 'Adjunto'])->default('Titular');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('portaria')->nullable();
            $table->enum('status', ['Ativo', 'Inativo'])->default('Ativo');
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Índices
            $table->index('curso_id');
            $table->index('colaborador_id');
            $table->index(['curso_id', 'status']);
            $table->index(['curso_id', 'tipo', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordenadores_curso');
    }
};
