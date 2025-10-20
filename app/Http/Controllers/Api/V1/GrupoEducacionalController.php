<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Http\Requests\StoreGrupoEducacionalRequest;
use App\Http\Requests\UpdateGrupoEducacionalRequest;
use Illuminate\Http\JsonResponse;

class GrupoEducacionalController extends Controller
{
    /**
     * Listar todos os grupos educacionais
     */
    public function index(): JsonResponse
    {
        try {
            $grupos = GrupoEducacional::select('id', 'nome')
                ->orderBy('nome')
                ->get();
            
            return response()->json($grupos);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar grupos educacionais: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreGrupoEducacionalRequest $request)
    {
        $grupo = GrupoEducacional::create($request->validated());
        return response()->json($grupo->load('creator', 'updater'), 201);
    }

    public function show(GrupoEducacional $grupo)
    {
        return $grupo->load(['mantenedoras', 'creator', 'updater']);
    }

    public function update(UpdateGrupoEducacionalRequest $request, GrupoEducacional $grupo)
    {
        $grupo->update($request->validated());
        return response()->json($grupo->load('creator', 'updater'));
    }

    public function destroy(GrupoEducacional $grupo)
    {
        $grupo->delete();
        return response()->json(['message' => 'Grupo educacional excluído com sucesso']);
    }

    /**
     * ✅ LISTAR MANTENEDORAS DE UM GRUPO EDUCACIONAL
     */
    public function mantenedoras($id): JsonResponse
    {
        try {
            $mantenedoras = Mantenedora::where('grupo_educacional_id', $id)
                ->select('id', 'razao_social', 'nome_fantasia', 'cnpj')
                ->orderBy('razao_social')
                ->get();
            
            \Log::info("Grupo {$id} - Total de mantenedoras encontradas: " . $mantenedoras->count());
            
            return response()->json($mantenedoras);
        } catch (\Exception $e) {
            \Log::error('Erro ao listar mantenedoras do grupo ' . $id . ': ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}