<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InstituicaoAtoRegulatorio;
use App\Models\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

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
            'tipo_ato' => ['required', Rule::in([
                'Credenciamento', 
                'Recredenciamento', 
                'Descredenciamento',
                'Renovação de Reconhecimento',
                'Outro'
            ])],
            'numero_portaria' => 'required|string|max:255',
            'data_publicacao_dou' => 'required|date',
            'link_publicacao' => 'nullable|url',
            'data_validade_ato' => 'nullable|date|after_or_equal:data_publicacao_dou',
        ]);

        $ato = InstituicaoAtoRegulatorio::create($validatedData);
        return response()->json(['message' => 'Ato Regulatório criado!', 'data' => $ato], 201);
    }

    public function update(Request $request, InstituicaoAtoRegulatorio $atoRegulatorio): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'tipo_ato' => ['required', Rule::in([
                'Credenciamento', 
                'Recredenciamento', 
                'Descredenciamento',
                'Renovação de Reconhecimento',
                'Outro'
            ])],
            'numero_portaria' => 'required|string|max:255',
            'data_publicacao_dou' => 'required|date',
            'link_publicacao' => 'nullable|url',
            'data_validade_ato' => 'nullable|date|after_or_equal:data_publicacao_dou',
        ]);

        $atoRegulatorio->update($validatedData);
        return response()->json(['message' => 'Ato Regulatório atualizado!', 'data' => $atoRegulatorio]);
    }

    public function destroy(InstituicaoAtoRegulatorio $atoRegulatorio): JsonResponse
    {
        $atoRegulatorio->delete();
        return response()->json(null, 204);
    }
    public function alertas()
{
    $hoje = now();
    
    // Atos vencidos
    $vencidos = InstituicaoAtoRegulatorio::with(['instituicao', 'creator', 'updater'])
        ->where('data_validade_ato', '<', $hoje)
        ->orderBy('data_validade_ato', 'asc')
        ->get();
    
    // Atos a vencer em até 30 dias
    $aVencer30 = InstituicaoAtoRegulatorio::with(['instituicao', 'creator', 'updater'])
        ->whereBetween('data_validade_ato', [$hoje, $hoje->copy()->addDays(30)])
        ->orderBy('data_validade_ato', 'asc')
        ->get();
    
    // Atos vigentes (vigência futura além de 30 dias)
    $vigentes = InstituicaoAtoRegulatorio::with(['instituicao', 'creator', 'updater'])
        ->where('data_validade_ato', '>', $hoje->copy()->addDays(30))
        ->count();
    
    return response()->json([
        'vencidos' => $vencidos,
        'a_vencer_30_dias' => $aVencer30,
        'vigentes_count' => $vigentes,
        'total' => InstituicaoAtoRegulatorio::count(),
    ]);
}
}
