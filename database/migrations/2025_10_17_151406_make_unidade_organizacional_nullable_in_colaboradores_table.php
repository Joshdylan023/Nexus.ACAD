<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->string('unidade_organizacional_type')->nullable()->change();
            $table->unsignedBigInteger('unidade_organizacional_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->string('unidade_organizacional_type')->nullable(false)->change();
            $table->unsignedBigInteger('unidade_organizacional_id')->nullable(false)->change();
        });
    }
};
