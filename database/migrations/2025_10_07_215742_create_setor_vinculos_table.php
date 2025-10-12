<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setor_vinculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setor_id')->constrained('setores');
            
            $table->morphs('vinculavel');
            $table->foreignId('pai_id')->nullable()->constrained('setor_vinculos')->onDelete('set null');
            
            $table->foreignId('gestor_id')->nullable()->constrained('users');
            $table->enum('status', ['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])->default('Em Implantação');
            $table->string('centro_custo_sap')->nullable();
            $table->string('centro_resultado_sap')->nullable();
            $table->boolean('requer_portaria_nomeacao_gestor')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setor_vinculos');
    }
};