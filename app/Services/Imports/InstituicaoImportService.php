<?php

namespace App\Services\Imports;

use App\Models\Instituicao;
use App\Models\Mantenedora;

class InstituicaoImportService extends BaseImportService
{
    protected function getImportType(): string
    {
        return 'instituicoes';
    }

    protected function getRequiredColumns(): array
    {
        return ['razao_social', 'cnpj', 'mantenedora_cnpj'];
    }

    protected function getValidationRules(): array
{
    return [
        'razao_social' => 'required|string|max:255',
        'cnpj' => 'required|string|max:18',
        'mantenedora_cnpj' => 'required|string|max:18',
        'nome_fantasia' => 'nullable|string|max:255',
        'sigla' => 'nullable|string|max:20',
        'tipo_organizacao_academica' => 'nullable|string|max:100',
        'categoria_administrativa' => 'nullable|string|max:100',
        'codigo_mec' => 'nullable|string|max:20',
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

    // Busca a mantenedora
    $mantenedora = Mantenedora::where('cnpj', $validated['mantenedora_cnpj'])->first();
    
    if (!$mantenedora) {
        throw new \Exception("Mantenedora com CNPJ {$validated['mantenedora_cnpj']} não encontrada");
    }

    $validated['mantenedora_id'] = $mantenedora->id;
    unset($validated['mantenedora_cnpj']);
    
    // Monta endereco_sede com os novos campos
    $endereco_parts = array_filter([
        $validated['logradouro'] ?? null,
        $validated['numero'] ?? null,
        $validated['complemento'] ?? null,
        $validated['bairro'] ?? null,
        $validated['cidade'] ?? null,
        $validated['estado'] ?? null,
        $validated['cep'] ?? null
    ]);
    
    $validated['endereco_sede'] = !empty($endereco_parts) 
        ? implode(', ', $endereco_parts) 
        : 'A definir';

    $instituicao = Instituicao::updateOrCreate(
        ['cnpj' => $validated['cnpj']],
        $validated
    );

    return $instituicao->exists;
}
}