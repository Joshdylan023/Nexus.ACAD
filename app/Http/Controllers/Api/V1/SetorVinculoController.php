<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SetorVinculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SetorVinculoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'vinculavel_type' => 'required|string',
            'vinculavel_id' => 'required|integer',
        ]);

        $vinculos = SetorVinculo::where('vinculavel_type', $request->vinculavel_type)
            ->where('vinculavel_id', $request->vinculavel_id)
            ->with(['setor', 'gestor', 'pai.setor']) // Carrega as relações
            ->get();

        return response()->json($vinculos);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'vinculavel_id' => 'required|integer',
            'vinculavel_type' => 'required|string',
            'pai_id' => 'nullable|exists:setor_vinculos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            'requer_portaria_nomeacao_gestor' => 'required|boolean',
        ]);

        $vinculo = SetorVinculo::create($validatedData);
        return response()->json(['message' => 'Vínculo criado!', 'data' => $vinculo], 201);
    }

    public function all(): JsonResponse
    {
        $vinculos = SetorVinculo::with(['setor', 'vinculavel'])->get();
        return response()->json($vinculos);
    }

    public function destroy(SetorVinculo $setorVinculo): JsonResponse
    {
        $setorVinculo->delete();
        return response()->json(null, 204);
    }
}