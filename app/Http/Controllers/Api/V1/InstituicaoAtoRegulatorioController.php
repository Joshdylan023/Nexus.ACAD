<?php

namespace App\Http\Controllers\Api\V1; // Garanta que o namespace está correto

use App\Http\Controllers\Controller;
use App\Models\InstituicaoAtoRegulatorio;
use App\Models\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

// Garanta que o nome da classe está correto
class InstituicaoAtoRegulatorioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['instituicao_id' => 'required|exists:instituicoes,id']);
        
        $atos = InstituicaoAtoRegulatorio::where('instituicao_id', $request->instituicao_id)
                         ->orderBy('data_publicacao_dou', 'desc')
                         ->get();

        return response()->json($atos);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'tipo_ato' => ['required', Rule::in(['Credenciamento', 'Recredenciamento', 'Outro'])],
            'numero_portaria' => 'required|string|max:255',
            'data_publicacao_dou' => 'required|date',
            'link_publicacao' => 'nullable|url',
            'data_validade_ato' => 'nullable|date|after_or_equal:data_publicacao_dou',
        ]);

        $ato = InstituicaoAtoRegulatorio::create($validatedData);
        return response()->json(['message' => 'Ato Regulatório criado!', 'data' => $ato], 201);
    }

    public function destroy(InstituicaoAtoRegulatorio $atoRegulatorio): JsonResponse
    {
        $atoRegulatorio->delete();
        return response()->json(null, 204);
    }
}