<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Models\SetorVinculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class GrupoEducacionalSetorController extends Controller
{
    // Lista os setores vinculados a um grupo educacional específico
    public function index(GrupoEducacional $grupoEducacional): JsonResponse
    {
        try {
            // Buscar diretamente os vínculos com eager loading
            $vinculos = SetorVinculo::where('vinculavel_type', 'grupo_educacional')
                ->where('vinculavel_id', $grupoEducacional->id)
                ->with(['setor', 'gestor:id,name'])
                ->get();

            // Formatar no estilo esperado pelo frontend
            $setores = $vinculos->map(function ($vinculo) {
                $setor = $vinculo->setor;
                
                if (!$setor) {
                    return null;
                }
                
                // Adicionar dados do pivot ao setor
                $setor->pivot = (object)[
                    'id' => $vinculo->id,
                    'gestor_id' => $vinculo->gestor_id,
                    'gestor' => $vinculo->gestor,
                    'status' => $vinculo->status,
                    'centro_custo_sap' => $vinculo->centro_custo_sap,
                    'centro_resultado_sap' => $vinculo->centro_resultado_sap,
                    'requer_portaria_nomeacao_gestor' => $vinculo->requer_portaria_nomeacao_gestor,
                    'pai_id' => $vinculo->pai_id,
                    'vinculavel_type' => $vinculo->vinculavel_type,
                    'vinculavel_id' => $vinculo->vinculavel_id,
                    'setor_id' => $vinculo->setor_id,
                ];
                
                return $setor;
            })->filter()->values();

            return response()->json($setores);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar setores do grupo educacional: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Erro ao buscar setores: ' . $e->getMessage()], 500);
        }
    }

    // Vincula um ou mais setores a um grupo educacional
    public function store(Request $request, GrupoEducacional $grupoEducacional): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            'centro_custo_sap' => 'nullable|string|max:50',
            'centro_resultado_sap' => 'nullable|string|max:50',
            'requer_portaria_nomeacao_gestor' => 'nullable|boolean',
            'pai_id' => 'nullable|exists:setor_vinculos,id',
        ]);

        $grupoEducacional->setores()->attach($data['setor_id'], [
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
            'centro_custo_sap' => $data['centro_custo_sap'] ?? null,
            'centro_resultado_sap' => $data['centro_resultado_sap'] ?? null,
            'requer_portaria_nomeacao_gestor' => $data['requer_portaria_nomeacao_gestor'] ?? false,
            'pai_id' => $data['pai_id'] ?? null,
        ]);

        return response()->json(['message' => 'Setor vinculado com sucesso!'], 201);
    }

    // Atualiza os dados de um vínculo existente
    public function update(Request $request, GrupoEducacional $grupoEducacional, $setorId): JsonResponse
    {
        $data = $request->validate([
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            'centro_custo_sap' => 'nullable|string|max:50',
            'centro_resultado_sap' => 'nullable|string|max:50',
            'requer_portaria_nomeacao_gestor' => 'nullable|boolean',
            'pai_id' => 'nullable|exists:setor_vinculos,id',
        ]);

        $grupoEducacional->setores()->updateExistingPivot($setorId, [
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
            'centro_custo_sap' => $data['centro_custo_sap'] ?? null,
            'centro_resultado_sap' => $data['centro_resultado_sap'] ?? null,
            'requer_portaria_nomeacao_gestor' => $data['requer_portaria_nomeacao_gestor'] ?? false,
            'pai_id' => $data['pai_id'] ?? null,
        ]);

        return response()->json(['message' => 'Vínculo do setor atualizado!']);
    }

    // Desvincula um setor de um grupo educacional
    public function destroy(GrupoEducacional $grupoEducacional, $setorId): JsonResponse
    {
        $grupoEducacional->setores()->detach($setorId);
        return response()->json(null, 204);
    }
}
