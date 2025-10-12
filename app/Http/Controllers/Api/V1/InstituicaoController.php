<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class InstituicaoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Instituicao::with(['mantenedora', 'reitor'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'mantenedora_id' => 'required|exists:mantenedoras,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'cnpj' => 'required|string|unique:instituicoes,cnpj|max:18',
            'tipo_organizacao_academica' => ['required', Rule::in(['Faculdade', 'Centro Universitário', 'Universidade'])],
            'reitor_id' => 'nullable|exists:users,id',
            'endereco_sede' => 'required|string',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Extinção'])],
            'codigo_sap' => 'nullable|string|max:255',
            'codigo_emec' => 'nullable|string|unique:instituicoes,codigo_emec',
        ]);
        $instituicao = Instituicao::create($validatedData);
        return response()->json(['message' => 'Instituição criada com sucesso!', 'data' => $instituicao->load(['mantenedora', 'reitor'])], 201);
    }
    
    public function show(Instituicao $instituicao): JsonResponse
    {
        return response()->json($instituicao->load(['mantenedora', 'reitor']));
    }

    public function update(Request $request, Instituicao $instituicao): JsonResponse
    {
        $validatedData = $request->validate([
            'mantenedora_id' => 'required|exists:mantenedoras,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'cnpj' => ['required', 'string', Rule::unique('instituicoes')->ignore($instituicao->id), 'max:18'],
            'tipo_organizacao_academica' => ['required', Rule::in(['Faculdade', 'Centro Universitário', 'Universidade'])],
            'reitor_id' => 'nullable|exists:users,id',
            'endereco_sede' => 'required|string',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Extinção'])],
            'codigo_sap' => 'nullable|string|max:255',
            'codigo_emec' => ['nullable', 'string', Rule::unique('instituicoes')->ignore($instituicao->id)],
        ]);
        $instituicao->update($validatedData);
        return response()->json(['message' => 'Instituição atualizada com sucesso!', 'data' => $instituicao->load(['mantenedora', 'reitor'])]);
    }
    
    public function destroy(Instituicao $instituicao): JsonResponse
    {
        $instituicao->delete();
        return response()->json(null, 204);
    }
}