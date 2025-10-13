<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_event_id')->nullable()->constrained('system_events')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('import_type'); // grupos, mantenedoras, instituicoes, etc
            $table->string('file_name');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('errors')->nullable(); // Array de erros detalhados
            $table->json('summary')->nullable(); // Resumo da importação
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
