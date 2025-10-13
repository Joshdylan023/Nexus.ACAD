<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->text('endereco_sede')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->text('endereco_sede')->nullable(false)->change();
        });
    }
};
