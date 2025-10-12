<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ementa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmentaController extends Controller
{
    /**
     * Lista as ementas, obrigatoriamente filtradas por disciplina.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['disciplina_id' => 'required|exists:disciplinas,id']);

        $ementas = Ementa::where('disciplina_id', $request->disciplina_id)
            ->orderBy('versao', 'desc')
            ->get();

        return response()->json($ementas);
    }

    /**
     * Armazena uma nova Ementa (versão).
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'disciplina_id' => 'required|exists:disciplinas,id',
            'titulo' => 'required|string|max:255',
            'versao' => 'required|integer|min:1',
            'ementa_resumida' => 'nullable|string',
            'conteudo_detalhado' => 'required|string',
            'bibliografia' => 'nullable|string',
            'data_inicio_vigencia' => 'required|date',
            'data_fim_vigencia' => 'nullable|date|after_or_equal:data_inicio_vigencia',
        ]);

        $ementa = Ementa::create($validatedData);

        return response()->json(['message' => 'Ementa criada com sucesso!', 'data' => $ementa], 201);
    }

    /**
     * Exibe uma Ementa específica.
     */
    public function show(Ementa $ementa): JsonResponse
    {
        return response()->json($ementa->load('disciplina'));
    }

    /**
     * Atualiza uma Ementa existente.
     */
    public function update(Request $request, Ementa $ementa): JsonResponse
    {
        $validatedData = $request->validate([
            'disciplina_id' => 'required|exists:disciplinas,id',
            'titulo' => 'required|string|max:255',
            'versao' => 'required|integer|min:1',
            'ementa_resumida' => 'nullable|string',
            'conteudo_detalhado' => 'required|string',
            'bibliografia' => 'nullable|string',
            'data_inicio_vigencia' => 'required|date',
            'data_fim_vigencia' => 'nullable|date|after_or_equal:data_inicio_vigencia',
        ]);

        $ementa->update($validatedData);

        return response()->json(['message' => 'Ementa atualizada com sucesso!', 'data' => $ementa]);
    }

    /**
     * Remove uma Ementa.
     */
    public function destroy(Ementa $ementa): JsonResponse
    {
        $ementa->delete();

        return response()->json(null, 204);
    }
}
