<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->string('logradouro')->nullable()->after('codigo_mec');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->string('cep', 10)->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->dropColumn(['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep']);
        });
    }
};
