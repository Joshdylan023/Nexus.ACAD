<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('module')->after('user_id')->index(); // institucional, pessoas_acessos, academico, financeiro, sistema
            $table->text('description')->nullable()->after('new_values'); // Descrição legível da ação
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['module', 'description']);
        });
    }
};
