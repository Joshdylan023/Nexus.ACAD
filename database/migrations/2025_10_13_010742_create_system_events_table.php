<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_events', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['maintenance', 'import', 'backup', 'migration'])->default('maintenance');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->json('restricted_access')->nullable(); // IDs dos usuários autorizados durante manutenção
            $table->boolean('block_student_portal')->default(true);
            $table->boolean('block_teacher_portal')->default(true);
            $table->boolean('block_admin_portal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_events');
    }
};
