<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_integrations', function (Blueprint $table) {
            // ✅ Adicionar SOMENTE se não existir
            if (!Schema::hasColumn('hr_integrations', 'auto_sync_enabled')) {
                $table->boolean('auto_sync_enabled')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('hr_integrations', 'sync_frequency')) {
                $table->enum('sync_frequency', ['hourly', 'daily', 'weekly', 'monthly'])
                    ->default('daily')
                    ->after('auto_sync_enabled');
            }
            
            if (!Schema::hasColumn('hr_integrations', 'sync_time')) {
                $table->time('sync_time')->nullable()->after('sync_frequency');
            }
            
            if (!Schema::hasColumn('hr_integrations', 'sync_day')) {
                $table->integer('sync_day')->nullable()->after('sync_time')
                    ->comment('0=domingo, 1=segunda, ..., 6=sábado');
            }
            
            if (!Schema::hasColumn('hr_integrations', 'next_sync_at')) {
                $table->timestamp('next_sync_at')->nullable()->after('last_sync_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_integrations', function (Blueprint $table) {
            $columns = [
                'auto_sync_enabled',
                'sync_frequency',
                'sync_time',
                'sync_day',
                'next_sync_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('hr_integrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
