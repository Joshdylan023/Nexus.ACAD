<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SetorController extends Controller
{
    /**
     * Listar todos os setores
     */
    public function index(): JsonResponse
    {
        try {
            // ✅ APENAS COLUNAS QUE EXISTEM NA TABELA
            $setores = Setor::select('id', 'nome', 'sigla', 'tipo')
                ->orderBy('nome')
                ->get();
            
            return response()->json($setores);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar setores: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Armazena um novo Setor.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'nullable|string|max:20',
            'tipo' => ['required', Rule::in(['Corporativo', 'Institucional', 'Operacional'])]
        ]);
        
        $setor = Setor::create($validatedData);

        return response()->json(['message' => 'Setor criado com sucesso!', 'data' => $setor], 201);
    }

    /**
     * Exibe os detalhes de um Setor específico.
     */
    public function show(Setor $setor): JsonResponse
    {
        return response()->json($setor);
    }

    /**
     * Atualiza os dados de um Setor existente.
     */
    public function update(Request $request, Setor $setor): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'nullable|string|max:20',
            'tipo' => ['required', Rule::in(['Corporativo', 'Institucional', 'Operacional'])]
        ]);
        
        $setor->update($validatedData);

        return response()->json(['message' => 'Setor atualizado com sucesso!', 'data' => $setor]);
    }

    /**
     * Remove um Setor.
     */
    public function destroy(Setor $setor): JsonResponse
    {
        $setor->delete();

        return response()->json(['message' => 'Setor excluído com sucesso!'], 204);
    }
}
