<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Professor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Professor::with('usuario')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id|unique:professores,user_id',
            'matricula_funcional' => 'required|string|unique:professores,matricula_funcional|max:20',
            // ... outros campos ...
        ]);
        $professor = Professor::create($validatedData);

        return response()->json(['message' => 'Professor criado com sucesso!', 'data' => $professor->load('usuario')], 201);
    }

    public function show(Professor $professor): JsonResponse
    {
        return response()->json($professor->load('usuario', 'formacao'));
    }

    public function update(Request $request, Professor $professor): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'matricula_funcional' => ['required', 'string', Rule::unique('professores')->ignore($professor->id), 'max:20'],
            'regime_contratacao' => ['required', Rule::in(['CLT', 'PJ', 'Horista'])],
            'tipo_contrato' => 'required|string|max:255',
            'carga_horaria_contratual' => 'required|integer|min:1',
            'nivel_carreira' => 'required|string|max:255',
            'biografia' => 'nullable|string',
        ]);
        $professor->update($validatedData);

        return response()->json(['message' => 'Professor atualizado com sucesso!', 'data' => $professor->load('usuario')]);
    }

    public function destroy(Professor $professor): JsonResponse
    {
        $professor->delete();

        return response()->json(['message' => 'Professor excluído com sucesso!'], 204);
    }
}
