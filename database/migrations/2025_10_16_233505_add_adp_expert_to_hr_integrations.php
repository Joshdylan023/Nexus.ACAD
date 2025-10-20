<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE hr_integrations 
                DROP CONSTRAINT IF EXISTS hr_integrations_provider_check
            ");
            
            DB::statement("
                ALTER TABLE hr_integrations 
                ADD CONSTRAINT hr_integrations_provider_check 
                CHECK (provider IN ('generic', 'totvs', 'sap', 'oracle', 'senior', 'adp', 'adp_expert', 'csv'))
            ");
        }
        // MySQL
        else {
            DB::statement("
                ALTER TABLE hr_integrations 
                MODIFY COLUMN provider ENUM('generic', 'totvs', 'sap', 'oracle', 'senior', 'adp', 'adp_expert', 'csv')
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE hr_integrations 
                DROP CONSTRAINT IF EXISTS hr_integrations_provider_check
            ");
            
            DB::statement("
                ALTER TABLE hr_integrations 
                ADD CONSTRAINT hr_integrations_provider_check 
                CHECK (provider IN ('generic', 'totvs', 'sap', 'oracle', 'senior', 'adp', 'csv'))
            ");
        }
        // MySQL
        else {
            DB::statement("
                ALTER TABLE hr_integrations 
                MODIFY COLUMN provider ENUM('generic', 'totvs', 'sap', 'oracle', 'senior', 'adp', 'csv')
            ");
        }
    }
};
