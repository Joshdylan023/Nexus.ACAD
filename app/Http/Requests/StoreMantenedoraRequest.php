<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidCnpj;

class StoreMantenedoraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grupo_educacional_id' => 'required|exists:grupos_educacionais,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => ['required', 'string', new ValidCnpj, 'unique:mantenedoras,cnpj'],
            'endereco_completo' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'grupo_educacional_id.required' => 'O grupo educacional é obrigatório.',
            'razao_social.required' => 'A razão social é obrigatória.',
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
    }
}
