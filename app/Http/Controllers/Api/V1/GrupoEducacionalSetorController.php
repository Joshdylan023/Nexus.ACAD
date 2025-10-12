<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Models\Setor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GrupoEducacionalSetorController extends Controller
{
    public function index(GrupoEducacional $grupo): JsonResponse
    {
        $vinculos = $grupo->setorVinculos()->with(['setor', 'gestor'])->get();

        $setores = $vinculos->map(function ($vinculo) {
            $setor = $vinculo->setor;
            if ($setor) {
                // Prepara os dados do pivô antes de atribuir
                $pivotData = $vinculo->toArray();
                unset($pivotData['setor']); // Remove o objeto setor para evitar redundância
                // Garante que o objeto gestor carregado seja o que vai no pivô
                $pivotData['gestor'] = $vinculo->gestor;
                $setor->pivot = $pivotData;
            }
            return $setor;
        })->filter(); // Garante que não haja setores nulos se o vínculo for inconsistente

        return response()->json($setores);
    }

    public function store(Request $request, GrupoEducacional $grupo): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Ativo,Inativo',
            'centro_custo_sap' => 'nullable|string|max:255',
            'centro_resultado_sap' => 'nullable|string|max:255',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        // Evitar duplicatas
        $exists = $grupo->setorVinculos()->where('setor_id', $data['setor_id'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Este setor já está vinculado a este grupo educacional.'], 409);
        }

        $grupo->setorVinculos()->create($data);

        return response()->json(['message' => 'Setor vinculado com sucesso!'], 201);
    }

    public function update(Request $request, GrupoEducacional $grupo, Setor $setor): JsonResponse
    {
        $data = $request->validate([
            'gestor_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Ativo,Inativo',
            'centro_custo_sap' => 'nullable|string|max:255',
            'centro_resultado_sap' => 'nullable|string|max:255',
            'requer_portaria_nomeacao_gestor' => 'boolean',
        ]);

        $vinculo = $grupo->setorVinculos()->where('setor_id', $setor->id)->firstOrFail();
        $vinculo->update($data);

        return response()->json(['message' => 'Vínculo atualizado com sucesso!']);
    }

    public function destroy(GrupoEducacional $grupo, Setor $setor): JsonResponse
    {
        $vinculo = $grupo->setorVinculos()->where('setor_id', $setor->id)->first();

        if ($vinculo) {
            $vinculo->delete();
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Vínculo não encontrado.'], 404);
    }
}