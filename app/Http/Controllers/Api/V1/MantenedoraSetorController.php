<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mantenedora;
use App\Models\Setor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MantenedoraSetorController extends Controller
{
    public function index(Mantenedora $mantenedora): JsonResponse
    {
        $vinculos = $mantenedora->vinculosDeSetor()->with(['setor', 'gestor'])->get();

        $setores = $vinculos->map(function ($vinculo) {
            $setor = $vinculo->setor;
            if ($setor) {
                $pivotData = $vinculo->toArray();
                unset($pivotData['setor']);
                $pivotData['gestor'] = $vinculo->gestor;
                $setor->pivot = $pivotData;
            }
            return $setor;
        })->filter();

        return response()->json($setores);
    }

    public function store(Request $request, Mantenedora $mantenedora): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'pai_id' => 'nullable|exists:setor_vinculos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Em Implantação,Ativo,Inativo,Suspenso,Concluído,Cancelado',
            'centro_custo_sap' => 'nullable|string|max:255',
            'centro_resultado_sap' => 'nullable|string|max:255',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        $exists = $mantenedora->vinculosDeSetor()->where('setor_id', $data['setor_id'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Este setor já está vinculado a esta mantenedora.'], 409);
        }

        $mantenedora->vinculosDeSetor()->create($data);

        return response()->json(['message' => 'Setor vinculado com sucesso!'], 201);
    }

    public function update(Request $request, Mantenedora $mantenedora, Setor $setor): JsonResponse
    {
        $data = $request->validate([
            'pai_id' => 'nullable|exists:setor_vinculos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Em Implantação,Ativo,Inativo,Suspenso,Concluído,Cancelado',
            'centro_custo_sap' => 'nullable|string|max:255',
            'centro_resultado_sap' => 'nullable|string|max:255',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        $vinculo = $mantenedora->vinculosDeSetor()->where('setor_id', $setor->id)->firstOrFail();
        $vinculo->update($data);

        return response()->json(['message' => 'Vínculo atualizado com sucesso!']);
    }

    public function destroy(Mantenedora $mantenedora, Setor $setor): JsonResponse
    {
        $vinculo = $mantenedora->vinculosDeSetor()->where('setor_id', $setor->id)->first();

        if ($vinculo) {
            $vinculo->delete();
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Vínculo não encontrado.'], 404);
    }
}
