<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidCnpj;

class StoreInstituicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mantenedora_id' => 'required|exists:mantenedoras,id',
            'reitor_id' => 'nullable|exists:users,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'cnpj' => ['required', 'string', new ValidCnpj, 'unique:instituicoes,cnpj'],
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
            'cep' => 'nullable|string|max:10',
            'status' => 'nullable|in:Ativo,Inativo,Em Extinção',
        ];
    }

    public function messages(): array
    {
        return [
            'mantenedora_id.required' => 'A mantenedora é obrigatória.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'nome_fantasia.required' => 'O nome fantasia é obrigatório.',
            'cnpj.required' => 'O CNPJ é obrigatório.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('cnpj')) {
            $this->merge([
                'cnpj' => preg_replace('/\D/', '', $this->cnpj),
            ]);
        }
        
        if ($this->has('cep')) {
            $this->merge([
                'cep' => preg_replace('/\D/', '', $this->cep),
            ]);
        }
    }
}
