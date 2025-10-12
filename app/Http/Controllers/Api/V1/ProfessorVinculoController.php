<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfessorVinculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessorVinculoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ProfessorVinculo::with(['usuario', 'instituicao'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'instituicao_id' => 'required|exists:instituicoes,id',
            'matricula_funcional' => [
                'required', 'string', 'max:255',
                Rule::unique('professor_vinculos')->where('instituicao_id', $request->instituicao_id),
            ],
            'status' => ['required', Rule::in(['Ativo', 'Afastado', 'Desligado'])],
            'regime_contratacao' => ['required', Rule::in(['CLT', 'PJ', 'Horista'])],
            'tipo_contrato' => 'required|string|max:255',
            'carga_horaria_contratual' => 'required|integer|min:0',
            'nivel_carreira' => 'required|string|max:255',
            'biografia' => 'nullable|string',
            'data_contratacao' => 'nullable|date',
            'data_afastamento' => 'nullable|date|after_or_equal:data_contratacao',
            'data_desligamento' => 'nullable|date|after_or_equal:data_contratacao',
        ]);

        $vinculo = ProfessorVinculo::create($validatedData);

        return response()->json(['message' => 'Vínculo de professor criado com sucesso!', 'data' => $vinculo->load(['usuario', 'instituicao'])], 201);
    }

    public function show(ProfessorVinculo $professorVinculo): JsonResponse
    {
        return response()->json($professorVinculo->load(['usuario', 'instituicao']));
    }

    public function update(Request $request, ProfessorVinculo $professorVinculo): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'instituicao_id' => 'required|exists:instituicoes,id',
            'matricula_funcional' => [
                'required', 'string', 'max:255',
                Rule::unique('professor_vinculos')->where('instituicao_id', $request->instituicao_id)->ignore($professorVinculo->id),
            ],
            'status' => ['required', Rule::in(['Ativo', 'Afastado', 'Desligado'])],
            'regime_contratacao' => ['required', Rule::in(['CLT', 'PJ', 'Horista'])],
            'tipo_contrato' => 'required|string|max:255',
            'carga_horaria_contratual' => 'required|integer|min:0',
            'nivel_carreira' => 'required|string|max:255',
            'biografia' => 'nullable|string',
            'data_contratacao' => 'nullable|date',
            'data_afastamento' => 'nullable|date|after_or_equal:data_contratacao',
            'data_desligamento' => 'nullable|date|after_or_equal:data_contratacao',
        ]);

        $professorVinculo->update($validatedData);

        return response()->json(['message' => 'Vínculo de professor atualizado com sucesso!', 'data' => $professorVinculo->load(['usuario', 'instituicao'])]);
    }

    public function destroy(ProfessorVinculo $professorVinculo): JsonResponse
    {
        $professorVinculo->delete();

        return response()->json(null, 204);
    }
}
