<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CursosAtoRegulatorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CursosAtoRegulatorioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CursosAtoRegulatorio::with('curso');

        if ($request->has('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        return response()->json($query->orderBy('data_publicacao_dou', 'desc')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'codigo_mec' => 'required|string|max:255',
            'codigo_emec' => 'nullable|string|max:255',
            'tipo_ato' => ['required', Rule::in(['Autorização', 'Reconhecimento', 'Renovação de Reconhecimento'])],
            'numero_portaria' => 'required|string|max:255',
            'data_publicacao_dou' => 'required|date',
            'link_publicacao' => 'nullable|url',
            'data_validade_ato' => 'nullable|date|after:data_publicacao_dou',
        ]);
        $ato = CursosAtoRegulatorio::create($validatedData);

        return response()->json(['message' => 'Ato Regulatório criado!', 'data' => $ato->load('curso')], 201);
    }

    public function show(CursosAtoRegulatorio $cursosAtoRegulatorio): JsonResponse
    {
        return response()->json($cursosAtoRegulatorio->load('curso'));
    }

    public function update(Request $request, CursosAtoRegulatorio $cursosAtoRegulatorio): JsonResponse
    {
        $validatedData = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'codigo_mec' => 'required|string|max:255',
            'codigo_emec' => 'nullable|string|max:255',
            'tipo_ato' => ['required', Rule::in(['Autorização', 'Reconhecimento', 'Renovação de Reconhecimento'])],
            'numero_portaria' => 'required|string|max:255',
            'data_publicacao_dou' => 'required|date',
            'link_publicacao' => 'nullable|url',
            'data_validade_ato' => 'nullable|date|after:data_publicacao_dou',
        ]);
        $cursosAtoRegulatorio->update($validatedData);

        return response()->json(['message' => 'Ato Regulatório atualizado!', 'data' => $cursosAtoRegulatorio->load('curso')]);
    }

    public function destroy(CursosAtoRegulatorio $cursosAtoRegulatorio): JsonResponse
    {
        $cursosAtoRegulatorio->delete();

        return response()->json(['message' => 'Ato Regulatório excluído!'], 204);
    }
}
