<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampusController extends Controller
{
    /**
     * Exibe uma lista de todos os Campi.
     */
    public function index(): JsonResponse
    {
        // Garante que as relações corretas sejam carregadas
        $campi = Campus::with(['instituicao', 'gerenteUnidade'])->get();

        return response()->json($campi);
    }

    /**
     * Armazena um novo Campus.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'nome' => 'required|string|max:255',
            'endereco_completo' => 'required|string',
            'gerente_unidade_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo'])],
        ]);
        $campus = Campus::create($validatedData);

        return response()->json(['message' => 'Campus criado com sucesso!', 'data' => $campus->load(['instituicao', 'gerenteUnidade'])], 201);
    }

    /**
     * Exibe os detalhes de um Campus específico.
     */
    public function show(Campus $campus): JsonResponse
    {
        return response()->json($campus->load(['instituicao', 'gerenteUnidade']));
    }

    /**
     * Atualiza os dados de um Campus existente.
     */
    public function update(Request $request, Campus $campus): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'nome' => 'required|string|max:255',
            'endereco_completo' => 'required|string',
            'gerente_unidade_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo'])],
        ]);
        $campus->update($validatedData);

        return response()->json(['message' => 'Campus atualizado com sucesso!', 'data' => $campus->load(['instituicao', 'gerenteUnidade'])]);
    }

    /**
     * Remove um Campus.
     */
    public function destroy(Campus $campus): JsonResponse
    {
        $campus->delete();

        return response()->json(null, 204);
    }
}
