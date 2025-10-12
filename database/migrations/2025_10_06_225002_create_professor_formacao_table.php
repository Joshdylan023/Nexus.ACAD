<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('professor_formacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_vinculo_id')->constrained('professor_vinculos')->onDelete('cascade');
            $table->enum('nivel', ['Graduação', 'Especialização', 'Mestrado', 'Doutorado', 'Pós-Doutorado']);
            $table->string('curso');
            $table->string('instituicao');
            $table->integer('ano_conclusao');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('professor_formacao'); }
};