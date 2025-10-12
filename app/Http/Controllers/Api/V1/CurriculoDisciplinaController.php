<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CurriculoDisciplina;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculoDisciplinaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['curriculo_id' => 'required|exists:curriculos,id']);

        $disciplinas = CurriculoDisciplina::with('disciplina')
            ->where('curriculo_id', $request->curriculo_id)
            ->orderBy('periodo_sugerido')
            ->get();

        return response()->json(['data' => $disciplinas]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'curriculo_id' => 'required|exists:curriculos,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'periodo_sugerido' => 'required|integer|min:1',
            'tipo_disciplina' => 'required|string|in:Obrigatória,Eletiva',
            'pre_requisitos' => 'nullable|string',
        ]);

        // Verifica se a disciplina já está no currículo
        if (CurriculoDisciplina::where('curriculo_id', $validatedData['curriculo_id'])
            ->where('disciplina_id', $validatedData['disciplina_id'])
            ->exists()) {
            return response()->json(['message' => 'Esta disciplina já foi adicionada ao currículo.'], 409); // 409 Conflict
        }

        $curriculoDisciplina = CurriculoDisciplina::create($validatedData);

        return response()->json(['message' => 'Disciplina adicionada ao currículo!', 'data' => $curriculoDisciplina->load('disciplina')], 201);
    }

    public function destroy($id): JsonResponse
    {
        $curriculoDisciplina = CurriculoDisciplina::find($id);
        if (! $curriculoDisciplina) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        }
        $curriculoDisciplina->delete();

        return response()->json(null, 204);
    }
}
