<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\SetorVinculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CampusSetorController extends Controller
{
    public function index(Campus $campus): JsonResponse
    {
        try {
            Log::info('Buscando setores para campus ID: ' . $campus->id);
            
            $vinculos = SetorVinculo::where('vinculavel_type', 'campus')
                ->where('vinculavel_id', $campus->id)
                ->with(['setor', 'gestor'])
                ->get();

            Log::info('Vínculos encontrados: ' . $vinculos->count());

            $vinculosFormatados = $vinculos->map(function ($vinculo) {
                if (!$vinculo->setor) {
                    Log::warning('Vínculo sem setor: ' . $vinculo->id);
                    return null;
                }

                return [
                    'id' => $vinculo->id,
                    'setor_id' => $vinculo->setor_id,
                    'gestor_id' => $vinculo->gestor_id,
                    'status' => $vinculo->status,
                    'centro_custo_sap' => $vinculo->centro_custo_sap,
                    'centro_resultado_sap' => $vinculo->centro_resultado_sap,
                    'requer_portaria_nomeacao_gestor' => $vinculo->requer_portaria_nomeacao_gestor,
                    'setor_pai_id' => $vinculo->pai_id,
                    'setor' => $vinculo->setor,
                    'gestor' => $vinculo->gestor,
                ];
            })->filter()->values();

            return response()->json($vinculosFormatados);
            
        } catch (\Exception $e) {
            Log::error('Erro em CampusSetorController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Erro ao buscar setores',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, Campus $campus): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            'centro_custo_sap' => 'nullable|string|max:50',
            'centro_resultado_sap' => 'nullable|string|max:50',
            'requer_portaria_nomeacao_gestor' => 'nullable|boolean',
            'setor_pai_id' => 'nullable|exists:setor_vinculos,id',
        ]);

        SetorVinculo::create([
            'setor_id' => $data['setor_id'],
            'vinculavel_type' => 'campus',
            'vinculavel_id' => $campus->id,
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
            'centro_custo_sap' => $data['centro_custo_sap'] ?? null,
            'centro_resultado_sap' => $data['centro_resultado_sap'] ?? null,
            'requer_portaria_nomeacao_gestor' => $data['requer_portaria_nomeacao_gestor'] ?? false,
            'pai_id' => $data['setor_pai_id'] ?? null,
        ]);

        return response()->json(['message' => 'Setor vinculado com sucesso!'], 201);
    }

    public function update(Request $request, Campus $campus, $setorId): JsonResponse
    {
        $data = $request->validate([
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            'centro_custo_sap' => 'nullable|string|max:50',
            'centro_resultado_sap' => 'nullable|string|max:50',
            'requer_portaria_nomeacao_gestor' => 'nullable|boolean',
            'setor_pai_id' => 'nullable|exists:setor_vinculos,id',
        ]);

        $vinculo = SetorVinculo::where('id', $setorId)
            ->where('vinculavel_type', 'campus')
            ->where('vinculavel_id', $campus->id)
            ->firstOrFail();

        $vinculo->update([
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
            'centro_custo_sap' => $data['centro_custo_sap'] ?? null,
            'centro_resultado_sap' => $data['centro_resultado_sap'] ?? null,
            'requer_portaria_nomeacao_gestor' => $data['requer_portaria_nomeacao_gestor'] ?? false,
            'pai_id' => $data['setor_pai_id'] ?? null,
        ]);

        return response()->json(['message' => 'Vínculo do setor atualizado!']);
    }

    public function destroy(Campus $campus, $setorId): JsonResponse
    {
        SetorVinculo::where('id', $setorId)
            ->where('vinculavel_type', 'campus')
            ->where('vinculavel_id', $campus->id)
            ->delete();

        return response()->json(null, 204);
    }
}
