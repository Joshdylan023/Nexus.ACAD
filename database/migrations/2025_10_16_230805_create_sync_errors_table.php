<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_errors', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento
            $table->foreignId('sync_log_id')->constrained()->onDelete('cascade');
            
            // Dados do erro
            $table->string('entity_type'); // colaborador, setor, etc
            $table->string('entity_id')->nullable(); // ID externo
            $table->string('error_code')->nullable();
            $table->text('error_message');
            $table->json('error_context')->nullable(); // Dados que causaram o erro
            
            // Resolução
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_errors');
    }
};
