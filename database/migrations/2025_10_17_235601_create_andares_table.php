<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('andares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloco_id')->constrained('blocos')->onDelete('cascade');
            $table->integer('numero'); // 0=Térreo, 1=1º, -1=Subsolo, etc
            $table->string('nome')->nullable(); // Ex: "Térreo", "1º Andar"
            $table->text('descricao')->nullable();
            $table->decimal('area_util', 10, 2)->nullable(); // m²
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
            $table->index(['bloco_id', 'numero']);
            $table->index('status');
            
            // Unique por bloco
            $table->unique(['bloco_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('andares');
    }
};
