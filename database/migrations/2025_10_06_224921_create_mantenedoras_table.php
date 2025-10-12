<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('mantenedoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_educacional_id')->nullable()->constrained('grupos_educacionais');
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->unique();
            $table->text('endereco_completo')->nullable();
            $table->string('representante_legal')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mantenedoras'); }
};