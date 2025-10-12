<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use App\Models\SetorVinculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstituicaoSetorController extends Controller
{
    public function index(Instituicao $instituicao): JsonResponse
    {
        $vinculos = $instituicao->setorVinculos()->with(['setor', 'gestor', 'pai'])->get();
        return response()->json($vinculos);
    }

    public function store(Request $request, Instituicao $instituicao): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'pai_id' => 'nullable|exists:setor_vinculos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'centro_custo_sap' => 'nullable|string',
            'centro_resultado_sap' => 'nullable|string',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        $vinculo = $instituicao->setorVinculos()->create($data);

        return response()->json($vinculo, 201);
    }

    public function update(Request $request, SetorVinculo $setorVinculo): JsonResponse
    {
        $data = $request->validate([
            'pai_id' => 'nullable|exists:setor_vinculos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'centro_custo_sap' => 'nullable|string',
            'centro_resultado_sap' => 'nullable|string',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        $setorVinculo->update($data);

        return response()->json($setorVinculo);
    }

    public function destroy(SetorVinculo $setorVinculo): JsonResponse
    {
        $setorVinculo->delete();

        return response()->json(null, 204);
    }
}
