<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Predio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\ExportaPdfTrait;
use Barryvdh\DomPDF\Facade\Pdf;

class PredioController extends Controller
{
    use ExportaPdfTrait;

    /**
     * Lista todos os prédios
     */
    public function index(Request $request)
{
    try {
        $query = Predio::with(['campus.instituicao', 'blocos', 'createdBy', 'updatedBy']);

        // ⭐ NOVO: FILTRO POR INSTITUIÇÃO (através do campus)
        if ($request->filled('instituicao_id')) {
            $query->whereHas('campus', function($q) use ($request) {
                $q->where('instituicao_id', $request->instituicao_id);
            });
        }

        // Filtro por campus
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
                  ->orWhere('descricao', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('acessibilidade')) {
            $query->where('acessibilidade', $request->boolean('acessibilidade'));
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'nome');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Paginação
        $perPage = $request->get('per_page', 15);
        
        if ($request->has('all') && $request->boolean('all')) {
            $predios = $query->get();
            return response()->json($predios);
        }

        $predios = $query->paginate($perPage);

        return response()->json($predios);

    } catch (\Exception $e) {
        Log::error('Erro ao listar prédios: ' . $e->getMessage());
        return response()->json([
            'message' => 'Erro ao listar prédios',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Exibe um prédio específico
     */
    public function show($id)
    {
        try {
            $predio = Predio::with([
                'campus',
                'blocos.andares.espacosFisicos',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            // Estatísticas
            $predio->estatisticas = [
                'total_blocos' => $predio->blocos->count(),
                'total_andares' => $predio->blocos->sum(function($bloco) {
                    return $bloco->andares->count();
                }),
                'total_espacos' => $predio->total_espacos,
                'espacos_disponiveis' => $predio->blocos->sum(function($bloco) {
                    return $bloco->andares->sum(function($andar) {
                        return $andar->espacosFisicos()->where('status', 'Disponível')->count();
                    });
                }),
            ];

            return response()->json($predio);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar prédio: ' . $e->getMessage());
            return response()->json([
                'message' => 'Prédio não encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cria um novo prédio
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'campus_id' => 'required|exists:campi,id',
                'codigo' => 'required|string|max:50|unique:predios,codigo',
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'endereco' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'total_andares' => 'nullable|integer|min:0',
                'total_blocos' => 'nullable|integer|min:0',
                'ano_construcao' => 'nullable|integer|min:1800|max:' . date('Y'),
                'area_construida' => 'nullable|numeric|min:0',
                'acessibilidade' => 'boolean',
                'elevador' => 'boolean',
                'ar_condicionado' => 'boolean',
                'wifi' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção,Reforma',
                'fotos' => 'nullable|array',
                'documentos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $predio = Predio::create($data);

            Log::info('Prédio criado', ['predio_id' => $predio->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Prédio criado com sucesso',
                'data' => $predio->load('campus')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar prédio: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao criar prédio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um prédio
     */
    public function update(Request $request, $id)
    {
        try {
            $predio = Predio::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'campus_id' => 'required|exists:campi,id',
                'codigo' => 'required|string|max:50|unique:predios,codigo,' . $id,
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'endereco' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'total_andares' => 'nullable|integer|min:0',
                'total_blocos' => 'nullable|integer|min:0',
                'ano_construcao' => 'nullable|integer|min:1800|max:' . date('Y'),
                'area_construida' => 'nullable|numeric|min:0',
                'acessibilidade' => 'boolean',
                'elevador' => 'boolean',
                'ar_condicionado' => 'boolean',
                'wifi' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção,Reforma',
                'fotos' => 'nullable|array',
                'documentos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            // Remove o campo 'created_by' para evitar que ele seja atualizado
            unset($data['created_by']);

            $predio->update($data);

            Log::info('Prédio atualizado', ['predio_id' => $predio->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Prédio atualizado com sucesso',
                'data' => $predio->load('campus')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar prédio: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar prédio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um prédio
     */
    public function destroy($id)
    {
        try {
            $predio = Predio::findOrFail($id);

            // Verifica se há blocos vinculados
            if ($predio->blocos()->count() > 0) {
                return response()->json([
                    'message' => 'Não é possível excluir um prédio com blocos vinculados'
                ], 422);
            }

            $predio->delete();

            Log::info('Prédio excluído', ['predio_id' => $id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Prédio excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir prédio: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir prédio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna estatísticas gerais de prédios
     */
    public function estatisticas(Request $request)
    {
        try {
            $stats = [
                'total_predios' => Predio::count(),
                'predios_ativos' => Predio::where('status', 'Ativo')->count(),
                'predios_manutencao' => Predio::where('status', 'Manutenção')->count(),
                'predios_reforma' => Predio::where('status', 'Reforma')->count(),
                'com_acessibilidade' => Predio::where('acessibilidade', true)->count(),
                'com_elevador' => Predio::where('elevador', true)->count(),
                'area_total' => Predio::sum('area_construida'),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas de prédios: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    public function porCampus($campusId)
        {
            $predios = \App\Models\Predio::where('campus_id', $campusId)
                        ->select('id', 'nome')
                        ->orderBy('nome')
                        ->get();
            return response()->json($predios);
        }

        /**
     * Exportar relatório de prédios em PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            $query = Predio::with([
                'campus.instituicao',
                'blocos.andares',
            ]);

            // Filtros
            if ($request->filled('instituicao_id')) {
                $query->whereHas('campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->filled('campus_id')) {
                $query->where('campus_id', $request->campus_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
                });
            }

            $predios = $query->orderBy('nome')->get();

            // Buscar identidade visual
            [$identidadeVisual, $instituicao] = $this->buscarIdentidadeVisual(
                $request->instituicao_id,
                $predios->first()
            );

            $logoBase64 = $this->converterLogoBase64($identidadeVisual);

            $data = [
                'predios' => $predios,
                'instituicao' => $instituicao,
                'identidadeVisual' => $identidadeVisual,
                'logoBase64' => $logoBase64,
                'dataGeracao' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
                'filtros' => [
                    'instituicao' => $instituicao?->nome_fantasia ?? $instituicao?->razao_social,
                    'campus' => $request->campus_id ? \App\Models\Campus::find($request->campus_id)?->nome : null,
                ]
            ];

            $pdf = Pdf::loadView('relatorios.predios', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('relatorio-predios-' . now()->format('Y-m-d-His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório PDF de prédios: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar relatório',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

}

