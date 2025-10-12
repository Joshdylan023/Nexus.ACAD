<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Curriculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurriculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Curriculo::with(['curso'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nome_matriz' => 'required|string|max:255',
            'tipo_matriz' => ['required', Rule::in(['Fechada', 'Aberta'])],
            'codigo_curriculo' => 'required|string|unique:curriculos,codigo_curriculo|max:20',
            'data_inicio_vigencia' => 'required|date',
        ]);

        $curriculo = Curriculo::create($validatedData);

        return response()->json(['message' => 'Currículo criado com sucesso!', 'data' => $curriculo->load(['curso'])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Curriculo $curriculo): JsonResponse
    {
        return response()->json($curriculo->load(['curso']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curriculo $curriculo): JsonResponse
    {
        $validatedData = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nome_matriz' => 'required|string|max:255',
            'tipo_matriz' => ['required', Rule::in(['Fechada', 'Aberta'])],
            'codigo_curriculo' => ['required', 'string', Rule::unique('curriculos')->ignore($curriculo->id), 'max:20'],
            'data_inicio_vigencia' => 'required|date',
        ]);

        $curriculo->update($validatedData);

        return response()->json(['message' => 'Currículo atualizado com sucesso!', 'data' => $curriculo->load(['curso'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curriculo $curriculo): JsonResponse
    {
        $curriculo->delete();

        return response()->json(['message' => 'Currículo excluído com sucesso!'], 204);
    }
}
