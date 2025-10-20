<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_mappings', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento
            $table->foreignId('hr_integration_id')->constrained()->onDelete('cascade');
            
            // Mapeamento
            $table->string('entity_type'); // colaborador, setor, cargo
            $table->string('source_field'); // Campo do sistema externo
            $table->string('target_field'); // Campo do Nexus ACAD
            $table->string('transform_function')->nullable(); // Função de transformação
            $table->boolean('is_required')->default(false);
            $table->string('default_value')->nullable();
            
            // Validação
            $table->json('validation_rules')->nullable();
            
            $table->timestamps();
            
            $table->unique(['hr_integration_id', 'entity_type', 'target_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_mappings');
    }
};
