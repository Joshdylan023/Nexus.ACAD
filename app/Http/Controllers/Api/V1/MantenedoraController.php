<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use App\Http\Requests\StoreMantenedoraRequest;
use App\Http\Requests\UpdateMantenedoraRequest;

class MantenedoraController extends Controller
{
    public function index()
    {
        return Mantenedora::with(['grupoEducacional', 'instituicoes', 'creator', 'updater'])->get();
    }

    public function store(StoreMantenedoraRequest $request)
    {
        $mantenedora = Mantenedora::create($request->validated());
        return response()->json($mantenedora->load(['grupoEducacional', 'creator', 'updater']), 201);
    }

    public function show(Mantenedora $mantenedora)
    {
        return $mantenedora->load(['grupoEducacional', 'instituicoes', 'creator', 'updater']);
    }

    public function update(UpdateMantenedoraRequest $request, Mantenedora $mantenedora)
    {
        $mantenedora->update($request->validated());
        return response()->json($mantenedora->load(['grupoEducacional', 'creator', 'updater']));
    }

    public function destroy(Mantenedora $mantenedora)
    {
        $mantenedora->delete();
        return response()->json(['message' => 'Mantenedora excluída com sucesso']);
    }

    /**
     * Listar instituições de uma mantenedora
     */
    public function instituicoes($id): JsonResponse
{
    try {
        // ✅ CORRIGIDO - Instituições têm razao_social, nome_fantasia e sigla
        $instituicoes = Instituicao::where('mantenedora_id', $id)
            ->select('id', 'razao_social', 'nome_fantasia', 'sigla')
            ->orderBy('sigla')
            ->get();
        
        return response()->json($instituicoes);
    } catch (\Exception $e) {
        \Log::error('Erro ao listar instituições: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
