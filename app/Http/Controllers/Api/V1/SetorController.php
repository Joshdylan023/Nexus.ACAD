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
     * Exibe uma lista de todos os Setores.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setor::orderBy('nome');

        // Se a requisição pedir tipos específicos, filtra por eles
        if ($request->has('tipos')) {
            $tipos = explode(',', $request->tipos);
            $query->whereIn('tipo', $tipos);
        }

        return response()->json($query->get());
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
        return response()->json($setor->load(['campus', 'gestor', 'pai', 'filhos']));
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

        return response()->json(['message' => 'Setor atualizado com sucesso!', 'data' => $setor->load(['campus', 'gestor', 'pai'])]);
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
