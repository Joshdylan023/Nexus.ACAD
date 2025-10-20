<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\CursosAtoRegulatorio;
use App\Helpers\IdentidadeVisualHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CursosAtosRegulatoriosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class CursosAtoRegulatorioController extends Controller
{
    /**
     * Listar todos os atos regulatórios de cursos
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = CursosAtoRegulatorio::with(['curso']);

        // Filtrar por curso específico
        if ($request->has('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        // Filtrar por tipo de ato
        if ($request->has('tipo_ato')) {
            $query->where('tipo_ato', $request->tipo_ato);
        }

        // Filtrar por status de validade
        if ($request->has('status')) {
            switch ($request->status) {
                case 'vencido':
                    $query->whereNotNull('data_validade_ato')
                          ->where('data_validade_ato', '<', now());
                    break;
                case 'ativo':
                    $query->where(function($q) {
                        $q->whereNull('data_validade_ato')
                          ->orWhere('data_validade_ato', '>=', now());
                    });
                    break;
                case 'expirando':
                    $query->whereNotNull('data_validade_ato')
                          ->whereBetween('data_validade_ato', [now(), now()->addDays(90)]);
                    break;
            }
        }

        // Ordenar
        $query->orderBy('data_publicacao_dou', 'desc');

        $atos = $query->get();

        return response()->json($atos);
    }

    /**
     * Criar novo ato regulatório
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'curso_id' => 'required|exists:cursos,id',
            'tipo_ato' => 'required|in:Autorização,Reconhecimento,Renovação de Reconhecimento',
            'codigo_mec' => 'required|string|max:50',
            'codigo_emec' => 'nullable|string|max:50',
            'numero_portaria' => 'required|string|max:100',
            'data_publicacao_dou' => 'required|date',
            'data_validade_ato' => 'nullable|date|after:data_publicacao_dou',
            'link_publicacao' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $ato = CursosAtoRegulatorio::create($request->all());

        return response()->json([
            'message' => 'Ato regulatório criado com sucesso',
            'data' => $ato->load('curso')
        ], 201);
    }

    /**
     * Exibir um ato regulatório específico
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $ato = CursosAtoRegulatorio::with(['curso'])->find($id);

        if (!$ato) {
            return response()->json([
                'message' => 'Ato regulatório não encontrado'
            ], 404);
        }

        return response()->json($ato);
    }

    /**
     * Atualizar ato regulatório
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $ato = CursosAtoRegulatorio::find($id);

        if (!$ato) {
            return response()->json([
                'message' => 'Ato regulatório não encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'curso_id' => 'sometimes|required|exists:cursos,id',
            'tipo_ato' => 'sometimes|required|in:Autorização,Reconhecimento,Renovação de Reconhecimento',
            'codigo_mec' => 'sometimes|required|string|max:50',
            'codigo_emec' => 'nullable|string|max:50',
            'numero_portaria' => 'sometimes|required|string|max:100',
            'data_publicacao_dou' => 'sometimes|required|date',
            'data_validade_ato' => 'nullable|date|after:data_publicacao_dou',
            'link_publicacao' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $ato->update($request->all());

        return response()->json([
            'message' => 'Ato regulatório atualizado com sucesso',
            'data' => $ato->load('curso')
        ]);
    }

    /**
     * Deletar ato regulatório
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $ato = CursosAtoRegulatorio::find($id);

        if (!$ato) {
            return response()->json([
                'message' => 'Ato regulatório não encontrado'
            ], 404);
        }

        $ato->delete();

        return response()->json([
            'message' => 'Ato regulatório excluído com sucesso'
        ]);
    }

    /**
     * Exportar atos regulatórios para Excel
     */
    public function exportExcel(Curso $curso)
    {
        $fileName = 'atos_regulatorios_' . Str::slug($curso->nome) . '_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new CursosAtosRegulatoriosExport($curso->id), 
            $fileName
        );
    }

    /**
     * Exportar atos regulatórios para PDF
     */
    public function exportPDF(Curso $curso)
    {
        // Carregar curso com instituição
        $curso->load(['instituicao', 'campus', 'coordenador']);
        
        $atos = CursosAtoRegulatorio::where('curso_id', $curso->id)
            ->orderBy('data_publicacao_dou', 'desc')
            ->get();
        
        // ✅ Buscar identidade visual usando o helper
        $identidade = IdentidadeVisualHelper::buscarPorInstituicao($curso->instituicao);
        
        $pdf = Pdf::loadView('exports.cursos-atos-regulatorios-pdf', [
            'curso' => $curso,
            'atos' => $atos,
            'identidade' => $identidade
        ]);
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('enable-local-file-access', true); // ✅ Para carregar imagens locais
        
        $fileName = 'atos_regulatorios_' . Str::slug($curso->nome) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Buscar atos que estão próximos de vencer
     */
    public function atosExpirando(Request $request): \Illuminate\Http\JsonResponse
    {
        $dias = $request->get('dias', 90);

        $atos = CursosAtoRegulatorio::with(['curso'])
            ->whereNotNull('data_validade_ato')
            ->whereBetween('data_validade_ato', [now(), now()->addDays($dias)])
            ->orderBy('data_validade_ato', 'asc')
            ->get();

        return response()->json($atos);
    }

    /**
     * Buscar atos vencidos
     */
    public function atosVencidos(): \Illuminate\Http\JsonResponse
    {
        $atos = CursosAtoRegulatorio::with(['curso'])
            ->whereNotNull('data_validade_ato')
            ->where('data_validade_ato', '<', now())
            ->orderBy('data_validade_ato', 'desc')
            ->get();

        return response()->json($atos);
    }

    /**
     * Dashboard com estatísticas dos atos
     */
    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        $total = CursosAtoRegulatorio::count();
        $ativos = CursosAtoRegulatorio::where(function($q) {
            $q->whereNull('data_validade_ato')
              ->orWhere('data_validade_ato', '>=', now());
        })->count();
        
        $expirando = CursosAtoRegulatorio::whereNotNull('data_validade_ato')
            ->whereBetween('data_validade_ato', [now(), now()->addDays(90)])
            ->count();
        
        $vencidos = CursosAtoRegulatorio::whereNotNull('data_validade_ato')
            ->where('data_validade_ato', '<', now())
            ->count();

        $porTipo = CursosAtoRegulatorio::selectRaw('tipo_ato, COUNT(*) as total')
            ->groupBy('tipo_ato')
            ->get();

        return response()->json([
            'total' => $total,
            'ativos' => $ativos,
            'expirando' => $expirando,
            'vencidos' => $vencidos,
            'por_tipo' => $porTipo
        ]);
    }
}
