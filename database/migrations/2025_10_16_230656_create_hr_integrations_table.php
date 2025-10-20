<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_integrations', function (Blueprint $table) {
            $table->id();
            
            // Configuração básica
            $table->string('name'); // Ex: "Integração TOTVS - Matriz"
            $table->enum('provider', [
                'generic',
                'totvs',
                'sap',
                'oracle',
                'senior',
                'adp',
                'csv'
            ]);
            $table->boolean('is_active')->default(false);
            
            // Configurações de conexão (criptografadas)
            $table->text('config')->nullable(); // JSON criptografado
            
            // Configurações de sincronização
            $table->enum('sync_frequency', ['manual', 'hourly', 'daily', 'weekly'])->default('manual');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();
            
            // Mapeamento de campos
            $table->json('field_mapping')->nullable();
            
            // Filtros e opções
            $table->json('sync_options')->nullable(); // Quais dados sincronizar
            
            // Auditoria
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_integrations');
    }
};
