<?php

namespace App\Services\Imports;

use App\Models\GrupoEducacional;

class GrupoEducacionalImportService extends BaseImportService
{
    protected function getImportType(): string
    {
        return 'grupos_educacionais';
    }

    protected function getRequiredColumns(): array
    {
        return ['nome', 'cnpj'];
    }

    protected function getValidationRules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18',
            'descricao' => 'nullable|string'
        ];
    }

    protected function processRow(array $row): bool
    {
        // Remove campos que não existem na tabela
        $data = [
            'nome' => $row['nome'],
            'cnpj' => $row['cnpj'],
            'descricao' => $row['descricao'] ?? null
        ];

        $this->validateRow($data);

        // Verifica se já existe (atualiza ao invés de criar)
        $grupo = GrupoEducacional::updateOrCreate(
            ['cnpj' => $data['cnpj']],
            $data
        );

        return $grupo->exists;
    }
}
