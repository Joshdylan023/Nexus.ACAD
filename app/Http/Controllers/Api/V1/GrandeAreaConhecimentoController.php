<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrandeAreaConhecimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrandeAreaConhecimentoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(GrandeAreaConhecimento::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|unique:grandes_areas_conhecimento|max:255',
        ]);
        $grandeArea = GrandeAreaConhecimento::create($validatedData);

        return response()->json(['message' => 'Grande Área de Conhecimento criada!', 'data' => $grandeArea], 201);
    }

    public function show(GrandeAreaConhecimento $grandeAreaConhecimento): JsonResponse
    {
        return response()->json($grandeAreaConhecimento);
    }

    public function update(Request $request, GrandeAreaConhecimento $grandeAreaConhecimento): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => ['required', 'string', Rule::unique('grandes_areas_conhecimento')->ignore($grandeAreaConhecimento->id), 'max:255'],
        ]);
        $grandeAreaConhecimento->update($validatedData);

        return response()->json(['message' => 'Grande Área de Conhecimento atualizada!', 'data' => $grandeAreaConhecimento]);
    }

    public function destroy(GrandeAreaConhecimento $grandeAreaConhecimento): JsonResponse
    {
        $grandeAreaConhecimento->delete();

        return response()->json(['message' => 'Grande Área de Conhecimento excluída!'], 204);
    }
}
