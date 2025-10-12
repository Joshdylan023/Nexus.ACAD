<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AreaConhecimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaConhecimentoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AreaConhecimento::with('grandeArea')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'grande_area_conhecimento_id' => 'required|exists:grandes_areas_conhecimento,id',
            'nome' => 'required|string|unique:areas_conhecimento|max:255',
        ]);
        $areaConhecimento = AreaConhecimento::create($validatedData);

        return response()->json(['message' => 'Área de Conhecimento criada!', 'data' => $areaConhecimento->load('grandeArea')], 201);
    }

    public function show(AreaConhecimento $areaConhecimento): JsonResponse
    {
        return response()->json($areaConhecimento->load('grandeArea'));
    }

    public function update(Request $request, AreaConhecimento $areaConhecimento): JsonResponse
    {
        $validatedData = $request->validate([
            'grande_area_conhecimento_id' => 'required|exists:grandes_areas_conhecimento,id',
            'nome' => ['required', 'string', Rule::unique('areas_conhecimento')->ignore($areaConhecimento->id), 'max:255'],
        ]);
        $areaConhecimento->update($validatedData);

        return response()->json(['message' => 'Área de Conhecimento atualizada!', 'data' => $areaConhecimento->load('grandeArea')]);
    }

    public function destroy(AreaConhecimento $areaConhecimento): JsonResponse
    {
        $areaConhecimento->delete();

        return response()->json(['message' => 'Área de Conhecimento excluída!'], 204);
    }
}
