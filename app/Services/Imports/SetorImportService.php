<?php

namespace App\Services\Imports;

use App\Models\Setor;

class SetorImportService extends BaseImportService
{
    protected function getImportType(): string
    {
        return 'setores';
    }

    protected function getRequiredColumns(): array
    {
        return ['nome', 'tipo'];
    }

    protected function getValidationRules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:Corporativo,Institucional',
            'descricao' => 'nullable|string',
            'nivel' => 'nullable|integer|min:1|max:10'
        ];
    }

    protected function processRow(array $row): bool
    {
        $validated = $this->validateRow($row);

        $setor = Setor::updateOrCreate(
            ['nome' => $validated['nome']],
            $validated
        );

        return $setor->exists;
    }
}
