<?php

namespace App\Services\Imports;

use App\Models\Mantenedora;
use App\Models\GrupoEducacional;

class MantenedoraImportService extends BaseImportService
{
    protected function getImportType(): string
    {
        return 'mantenedoras';
    }

    protected function getRequiredColumns(): array
    {
        return ['razao_social', 'cnpj', 'grupo_educacional_cnpj'];
    }

    protected function getValidationRules(): array
    {
        return [
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18',
            'grupo_educacional_cnpj' => 'required|string|max:18',
            'nome_fantasia' => 'nullable|string|max:255',
            'inscricao_estadual' => 'nullable|string|max:20',
            'inscricao_municipal' => 'nullable|string|max:20'
        ];
    }

    protected function processRow(array $row): bool
    {
        $validated = $this->validateRow($row);

        // Busca o grupo educacional
        $grupo = GrupoEducacional::where('cnpj', $validated['grupo_educacional_cnpj'])->first();
        
        if (!$grupo) {
            throw new \Exception("Grupo Educacional com CNPJ {$validated['grupo_educacional_cnpj']} não encontrado");
        }

        $validated['grupo_educacional_id'] = $grupo->id;
        unset($validated['grupo_educacional_cnpj']);

        $mantenedora = Mantenedora::updateOrCreate(
            ['cnpj' => $validated['cnpj']],
            $validated
        );

        return $mantenedora->exists;
    }
}
