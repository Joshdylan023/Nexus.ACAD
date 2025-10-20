<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('hr_integrations')
            ->where('id', 1)
            ->update([
                'field_mapping' => json_encode([
                    [
                        'entity_type' => 'colaborador',
                        'source_field' => 'matricula',
                        'target_field' => 'matricula_funcional',
                        'is_required' => true,
                    ],
                    [
                        'entity_type' => 'colaborador',
                        'source_field' => 'nome',
                        'target_field' => 'nome_completo',
                        'is_required' => true,
                    ],
                    [
                        'entity_type' => 'colaborador',
                        'source_field' => 'cpf',
                        'target_field' => 'cpf',
                        'is_required' => true,
                    ],
                    [
                        'entity_type' => 'colaborador',
                        'source_field' => 'email',
                        'target_field' => 'email',
                        'is_required' => false,
                    ],
                ])
            ]);
    }

    public function down()
    {
        DB::table('hr_integrations')
            ->where('id', 1)
            ->update(['field_mapping' => null]);
    }
};
