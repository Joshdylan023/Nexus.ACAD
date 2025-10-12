<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    /**
     * Exibe uma lista de todos os Cursos.
     */
    public function index(): JsonResponse
    {
        // AQUI ESTÁ A CORREÇÃO: Usamos with() para carregar as relações
        return response()->json(Curso::with(['instituicao', 'areaConhecimento', 'coordenador'])->get());
    }

    /**
     * Armazena um novo Curso.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'area_conhecimento_id' => 'required|exists:areas_conhecimento,id',
            'nome' => 'required|string|max:255',
            'codigo_interno' => 'required|string|unique:cursos,codigo_interno|max:20',
            'nivel' => ['required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação', 'Mestrado', 'Doutorado', 'Extensão', 'Livre',
            ])],
            'duracao_padrao_semestres' => 'required|integer|min:1',
            'prazo_maximo_semestres' => 'required|integer|min:1',
            'coordenador_id' => 'nullable|exists:users,id',
            'status' => ['nullable', Rule::in(['Em Planejamento', 'Ativo', 'Em Extinção', 'Extinto'])],
            'vagas_anuais' => 'required|integer|min:0',
        ]);
        $curso = Curso::create($validatedData);

        return response()->json(['message' => 'Curso criado com sucesso!', 'data' => $curso->load(['instituicao', 'areaConhecimento', 'coordenador'])], 201);
    }

    public function show(Curso $curso): JsonResponse
    {
        return response()->json($curso->load(['instituicao', 'areaConhecimento', 'coordenador']));
    }

    public function update(Request $request, Curso $curso): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'area_conhecimento_id' => 'required|exists:areas_conhecimento,id',
            'nome' => 'required|string|max:255',
            'codigo_interno' => ['required', 'string', Rule::unique('cursos')->ignore($curso->id), 'max:20'],
            'nivel' => ['required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação', 'Mestrado', 'Doutorado', 'Extensão', 'Livre',
            ])],
            'duracao_padrao_semestres' => 'required|integer|min:1',
            'prazo_maximo_semestres' => 'required|integer|min:1',
            'coordenador_id' => 'nullable|exists:users,id',
            'status' => ['nullable', Rule::in(['Em Planejamento', 'Ativo', 'Em Extinção', 'Extinto'])],
            'vagas_anuais' => 'required|integer|min:0',
        ]);
        $curso->update($validatedData);

        return response()->json(['message' => 'Curso atualizado com sucesso!', 'data' => $curso->load(['instituicao', 'areaConhecimento', 'coordenador'])]);
    }

    public function destroy(Curso $curso): JsonResponse
    {
        $curso->delete();

        return response()->json(['message' => 'Curso excluído com sucesso!'], 204);
    }
}
