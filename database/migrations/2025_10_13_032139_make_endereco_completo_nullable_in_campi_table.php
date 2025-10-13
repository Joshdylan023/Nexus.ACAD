<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campi', function (Blueprint $table) {
            $table->text('endereco_completo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('campi', function (Blueprint $table) {
            $table->text('endereco_completo')->nullable(false)->change();
        });
    }
};
