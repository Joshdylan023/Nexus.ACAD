<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CampusSetorController extends Controller
{
    // Lista os setores vinculados a um campus específico
    public function index(Campus $campus): JsonResponse
    {
        // Usamos a relação direta com SetorVinculo para ter mais controle
        $vinculos = $campus->setorVinculos()->with(['setor', 'gestor', 'pai.setor'])->get();

        $setores = $vinculos->map(function ($vinculo) {
            $setor = $vinculo->setor;
            if ($setor) {
                $setor->pivot = $vinculo->toArray();
            }
            return $setor;
        })->filter();
        return response()->json($setores);
    }

    // Vincula um ou mais setores a um campus
    public function store(Request $request, Campus $campus): JsonResponse
    {
        $data = $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
            // Adicionar outras validações se necessário (SAP, etc.)
        ]);

        // O segundo argumento do attach() são os dados extras da tabela-pivô
        $campus->setores()->attach($data['setor_id'], [
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
        ]);

        return response()->json(['message' => 'Setor vinculado com sucesso!'], 201);
    }

    // Atualiza os dados de um vínculo existente
    public function update(Request $request, Campus $campus, Setor $setor): JsonResponse
    {
        $data = $request->validate([
            'gestor_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo', 'Em Desativação', 'Em Implantação'])],
        ]);

        // updateExistingPivot() atualiza os dados da tabela-pivô
        $campus->setores()->updateExistingPivot($setor->id, [
            'gestor_id' => $data['gestor_id'] ?? null,
            'status' => $data['status'],
        ]);

        return response()->json(['message' => 'Vínculo do setor atualizado!']);
    }

    // Desvincula um setor de um campus
    public function destroy(Campus $campus, Setor $setor): JsonResponse
    {
        $campus->setores()->detach($setor->id);
        return response()->json(null, 204);
    }
}