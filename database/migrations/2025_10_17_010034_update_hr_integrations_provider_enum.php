<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL
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

    public function down(): void
    {
        DB::statement("
            ALTER TABLE hr_integrations 
            DROP CONSTRAINT IF EXISTS hr_integrations_provider_check
        ");
        
        DB::statement("
            ALTER TABLE hr_integrations 
            ADD CONSTRAINT hr_integrations_provider_check 
            CHECK (provider IN ('generic', 'totvs', 'adp', 'adp_expert'))
        ");
    }
};
