<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campi')->onDelete('cascade');
            $table->string('codigo', 50)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('endereco')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('total_andares')->default(0);
            $table->integer('total_blocos')->default(0);
            $table->integer('ano_construcao')->nullable();
            $table->decimal('area_construida', 10, 2)->nullable(); // m²
            $table->boolean('acessibilidade')->default(false);
            $table->boolean('elevador')->default(false);
            $table->boolean('ar_condicionado')->default(false);
            $table->boolean('wifi')->default(false);
            $table->enum('status', ['Ativo', 'Inativo', 'Manutenção', 'Reforma'])->default('Ativo');
            $table->json('fotos')->nullable(); // URLs das fotos
            $table->json('documentos')->nullable(); // PDFs, plantas, etc
            $table->text('observacoes')->nullable();
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('campus_id');
            $table->index('codigo');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predios');
    }
};
