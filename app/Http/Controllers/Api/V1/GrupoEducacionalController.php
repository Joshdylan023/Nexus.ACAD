<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GrupoEducacionalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(GrupoEducacional::orderBy('nome')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|unique:grupos_educacionais|max:255',
            'cnpj' => 'nullable|string|unique:grupos_educacionais,cnpj|max:18',
            'endereco_completo' => 'nullable|string',  // ← ADICIONADO
            'representante_legal' => 'nullable|string|max:255',
        ]);
        
        $grupo = GrupoEducacional::create($validatedData);
        return response()->json(['message' => 'Grupo Educacional criado com sucesso!', 'data' => $grupo], 201);
    }

    public function show(GrupoEducacional $grupo): JsonResponse
    {
        return response()->json($grupo);
    }

    public function update(Request $request, GrupoEducacional $grupo): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => ['required', 'string', Rule::unique('grupos_educacionais')->ignore($grupo->id), 'max:255'],
            'cnpj' => ['nullable', 'string', Rule::unique('grupos_educacionais')->ignore($grupo->id), 'max:18'],
            'endereco_completo' => 'nullable|string',  // ← ADICIONADO
            'representante_legal' => 'nullable|string|max:255',
        ]);
        
        $grupo->update($validatedData);
        return response()->json(['message' => 'Grupo Educacional atualizado com sucesso!', 'data' => $grupo]);
    }

    public function destroy(GrupoEducacional $grupo): JsonResponse
    {
        $grupo->delete();
        return response()->json(null, 204);
    }
}
