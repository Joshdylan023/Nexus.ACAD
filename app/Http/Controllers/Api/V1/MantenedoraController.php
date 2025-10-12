<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mantenedora;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MantenedoraController extends Controller
{
    /**
     * Exibe uma lista de todas as Mantenedoras.
     */
    public function index(): JsonResponse
    {
        $mantenedoras = Mantenedora::with('grupoEducacional')->get();

        return response()->json($mantenedoras);
    }

    /**
     * Armazena uma nova Mantenedora.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'grupo_educacional_id' => 'nullable|exists:grupos_educacionais,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => 'required|string|unique:mantenedoras,cnpj|max:18',
            'endereco_completo' => 'nullable|string',
            'representante_legal' => 'nullable|string|max:255',
        ]);
        $mantenedora = Mantenedora::create($validatedData);

        return response()->json(['message' => 'Mantenedora criada com sucesso!', 'data' => $mantenedora->load('grupoEducacional')], 201);
    }

    /**
     * Exibe os detalhes de uma Mantenedora específica.
     */
    public function show(Mantenedora $mantenedora): JsonResponse
    {
        return response()->json($mantenedora->load('grupoEducacional'));
    }

    /**
     * Atualiza os dados de uma Mantenedora existente.
     */
    public function update(Request $request, Mantenedora $mantenedora): JsonResponse
    {
        $validatedData = $request->validate([
            'grupo_educacional_id' => 'nullable|exists:grupos_educacionais,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => [
                'required',
                'string',
                Rule::unique('mantenedoras')->ignore($mantenedora->id),
                'max:18',
            ],
            'endereco_completo' => 'nullable|string',
            'representante_legal' => 'nullable|string|max:255',
        ]);
        $mantenedora->update($validatedData);

        return response()->json(['message' => 'Mantenedora atualizada com sucesso!', 'data' => $mantenedora->load('grupoEducacional')]);
    }

    /**
     * Remove uma Mantenedora.
     */
    public function destroy(Mantenedora $mantenedora): JsonResponse
    {
        $mantenedora->delete();

        return response()->json(null, 204);
    }
}
