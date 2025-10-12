<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfessorFormacao;
use App\Models\ProfessorVinculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfessorFormacaoController extends Controller
{
    /**
     * Lista as formações, obrigatoriamente filtradas por vínculo de professor.
     */
    public function index(Request $request): JsonResponse
    {
        $validatedData = $request->validate(['professor_id' => 'required|exists:professor_vinculos,id']);

        // Find the ProfessorVinculo to get the user_id
        $professorVinculo = ProfessorVinculo::find($validatedData['professor_id']);

        // Find the corresponding professor_id from the professores table using the user_id
        $professor = DB::table('professores')
            ->where('user_id', $professorVinculo->user_id)
            ->first();

        if (! $professor) {
            return response()->json([]); // No professor found, return empty array
        }

        $formacoes = ProfessorFormacao::where('professor_id', $professor->id)
            ->orderBy('ano_conclusao', 'desc')
            ->get();

        return response()->json($formacoes);
    }

    /**
     * Armazena uma nova formação acadêmica.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'professor_id' => 'required|exists:professor_vinculos,id', // Validate against professor_vinculos as sent from frontend
            'nivel' => ['required', Rule::in(['Graduação', 'Especialização', 'Mestrado', 'Doutorado', 'Pós-Doutorado'])],
            'curso' => 'required|string|max:255',
            'instituicao' => 'required|string|max:255',
            'ano_conclusao' => 'required|integer|digits:4',
        ]);

        // Find the ProfessorVinculo to get the user_id
        $professorVinculo = ProfessorVinculo::find($validatedData['professor_id']);

        // Find the corresponding professor_id from the professores table using the user_id
        $professor = DB::table('professores')
            ->where('user_id', $professorVinculo->user_id)
            ->first();

        if (! $professor) {
            return response()->json(['message' => 'Professor not found for the given professor_vinculo.'], 404);
        }

        $validatedData['professor_id'] = $professor->id; // Use the actual professor_id

        $formacao = ProfessorFormacao::create($validatedData);

        return response()->json(['message' => 'Formação adicionada com sucesso!', 'data' => $formacao], 201);
    }

    /**
     * Exibe uma formação específica.
     */
    public function show(ProfessorFormacao $formacao): JsonResponse
    {
        return response()->json($formacao->load('professor'));
    }

    /**
     * Atualiza uma formação existente. (Funcionalidade futura)
     */
    public function update(Request $request, ProfessorFormacao $formacao): JsonResponse
    {
        $validatedData = $request->validate([
            'professor_id' => 'required|exists:professor_vinculos,id',
            'nivel' => ['required', Rule::in(['Graduação', 'Especialização', 'Mestrado', 'Doutorado', 'Pós-Doutorado'])],
            'curso' => 'required|string|max:255',
            'instituicao' => 'required|string|max:255',
            'ano_conclusao' => 'required|integer|digits:4',
        ]);

        // Find the ProfessorVinculo to get the user_id
        $professorVinculo = ProfessorVinculo::find($validatedData['professor_id']);

        // Find the corresponding professor_id from the professores table using the user_id
        $professor = DB::table('professores')
            ->where('user_id', $professorVinculo->user_id)
            ->first();

        if (! $professor) {
            return response()->json(['message' => 'Professor not found for the given professor_vinculo.'], 404);
        }

        $validatedData['professor_id'] = $professor->id; // Use the actual professor_id

        $formacao->update($validatedData);

        return response()->json(['message' => 'Formação atualizada com sucesso!', 'data' => $formacao]);
    }

    /**
     * Remove uma formação.
     */
    public function destroy(ProfessorFormacao $formacao): JsonResponse
    {
        $formacao->delete();

        return response()->json(null, 204);
    }
}
