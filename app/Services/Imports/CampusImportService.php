<?php

namespace App\Services\Imports;

use App\Models\Campus;
use App\Models\Instituicao;

class CampusImportService extends BaseImportService
{
    protected function getImportType(): string
    {
        return 'campi';
    }

    protected function getRequiredColumns(): array
    {
        return ['nome', 'instituicao_cnpj'];
    }

    protected function getValidationRules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'instituicao_cnpj' => 'required|string|max:18',
            'sigla' => 'nullable|string|max:20',
            'codigo_inep' => 'nullable|string|max:20',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10'
        ];
    }

    protected function processRow(array $row): bool
    {
        $validated = $this->validateRow($row);

        // Busca a instituição
        $instituicao = Instituicao::where('cnpj', $validated['instituicao_cnpj'])->first();
        
        if (!$instituicao) {
            throw new \Exception("Instituição com CNPJ {$validated['instituicao_cnpj']} não encontrada");
        }

        $validated['instituicao_id'] = $instituicao->id;
        unset($validated['instituicao_cnpj']);
        
        // Monta endereco_completo se campos estiverem preenchidos
        $endereco_parts = array_filter([
            $validated['logradouro'] ?? null,
            $validated['numero'] ?? null,
            $validated['complemento'] ?? null,
            $validated['bairro'] ?? null,
            $validated['cidade'] ?? null,
            $validated['estado'] ?? null,
            $validated['cep'] ?? null
        ]);
        
        $validated['endereco_completo'] = !empty($endereco_parts) 
            ? implode(', ', $endereco_parts) 
            : 'A definir';

        // Para campus, usamos nome + instituicao_id como chave única
        $campus = Campus::updateOrCreate(
            [
                'nome' => $validated['nome'],
                'instituicao_id' => $validated['instituicao_id']
            ],
            $validated
        );

        return $campus->exists;
    }
}
