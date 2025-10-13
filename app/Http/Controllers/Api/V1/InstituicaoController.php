<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use Illuminate\Http\Request;

class InstituicaoController extends Controller
{
    public function index()
    {
        $instituicoes = Instituicao::with(['mantenedora', 'reitor'])->get();
        return response()->json($instituicoes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:instituicoes,cnpj',
            'nome_fantasia' => 'nullable|string|max:255',
            'sigla' => 'nullable|string|max:20',
            'tipo_organizacao_academica' => 'nullable|string|max:100',
            'categoria_administrativa' => 'nullable|string|max:100',
            'codigo_mec' => 'nullable|string|max:20',
            'endereco_sede' => 'nullable|string',
            'status' => 'nullable|string',
            'mantenedora_id' => 'required|exists:mantenedoras,id',
            'reitor_id' => 'nullable|exists:users,id',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10'
        ]);

        $instituicao = Instituicao::create($validated);

        return response()->json($instituicao, 201);
    }

    public function show(Instituicao $instituicao)
    {
        $instituicao->load(['mantenedora', 'reitor']);
        return response()->json($instituicao);
    }

    public function update(Request $request, Instituicao $instituicao)
    {
        $validated = $request->validate([
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:instituicoes,cnpj,' . $instituicao->id,
            'nome_fantasia' => 'nullable|string|max:255',
            'sigla' => 'nullable|string|max:20',
            'tipo_organizacao_academica' => 'nullable|string|max:100',
            'categoria_administrativa' => 'nullable|string|max:100',
            'codigo_mec' => 'nullable|string|max:20',
            'endereco_sede' => 'nullable|string',
            'status' => 'nullable|string',
            'mantenedora_id' => 'required|exists:mantenedoras,id',
            'reitor_id' => 'nullable|exists:users,id',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10'
        ]);

        $instituicao->update($validated);

        return response()->json($instituicao);
    }

    public function destroy(Instituicao $instituicao)
    {
        try {
            $instituicao->delete();
            return response()->json(['message' => 'Instituição excluída com sucesso']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir instituição',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
