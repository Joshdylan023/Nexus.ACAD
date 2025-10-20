<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('predio_id')->constrained('predios')->onDelete('cascade');
            $table->string('codigo', 50);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('total_andares')->default(0);
            $table->boolean('acessibilidade')->default(false);
            $table->enum('status', ['Ativo', 'Inativo', 'Manutenção'])->default('Ativo');
            $table->json('fotos')->nullable();
            $table->text('observacoes')->nullable();
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['predio_id', 'codigo']);
            $table->index('status');
            
            // Unique por prédio
            $table->unique(['predio_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocos');
    }
};
