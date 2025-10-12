<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Disciplina;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisciplinaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Disciplina::orderBy('nome')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|unique:disciplinas,codigo|max:20',
            'carga_horaria_total' => 'required|integer|min:1',
        ]);
        $disciplina = Disciplina::create($validatedData);

        return response()->json(['message' => 'Disciplina criada com sucesso!', 'data' => $disciplina], 201);
    }

    public function show(Disciplina $disciplina): JsonResponse
    {
        return response()->json($disciplina);
    }

    public function update(Request $request, Disciplina $disciplina): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => ['required', 'string', Rule::unique('disciplinas')->ignore($disciplina->id), 'max:20'],
            'carga_horaria_total' => 'required|integer|min:1',
        ]);
        $disciplina->update($validatedData);

        return response()->json(['message' => 'Disciplina atualizada!', 'data' => $disciplina]);
    }

    public function destroy(Disciplina $disciplina): JsonResponse
    {
        $disciplina->delete();

        return response()->json(null, 204);
    }
}
