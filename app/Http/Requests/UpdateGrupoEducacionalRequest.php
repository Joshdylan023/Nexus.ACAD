<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidCnpj;
use Illuminate\Validation\Rule;

class UpdateGrupoEducacionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cnpj' => [
                'required',
                'string',
                new ValidCnpj,
                Rule::unique('grupos_educacionais', 'cnpj')->ignore($this->route('grupo')),
            ],
            'endereco_completo' => 'nullable|string',
            'representante_legal' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do grupo é obrigatório.',
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
