<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EspacoFisico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EspacoFisicoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = EspacoFisico::with([
                'andar.bloco.predio.campus.instituicao',
                'responsavel',
                'createdBy',
                'updatedBy'
            ]);

            // ⭐ FILTRO POR ANDAR
            if ($request->has('andar_id')) {
                $query->where('andar_id', $request->andar_id);
            }

            // ⭐ NOVO: FILTRO POR BLOCO (através do andar)
            if ($request->filled('bloco_id')) {
                $query->whereHas('andar', function($q) use ($request) {
                    $q->where('bloco_id', $request->bloco_id);
                });
            }

            // ⭐ FILTRO POR PRÉDIO (através do andar e bloco)
            if ($request->has('predio_id')) {
                $query->whereHas('andar.bloco', function($q) use ($request) {
                    $q->where('predio_id', $request->predio_id);
                });
            }

            // ⭐ FILTRO POR CAMPUS (através do andar, bloco e prédio)
            if ($request->has('campus_id')) {
                $query->whereHas('andar.bloco.predio', function($q) use ($request) {
                    $q->where('campus_id', $request->campus_id);
                });
            }

            // ⭐ NOVO: FILTRO POR INSTITUIÇÃO (hierarquia completa)
            if ($request->filled('instituicao_id')) {
                $query->whereHas('andar.bloco.predio.campus', function($q) use ($request) {
                    $q->where('instituicao_id', $request->instituicao_id);
                });
            }

            // FILTRO POR TIPO
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // FILTRO POR STATUS
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // FILTRO POR CAPACIDADE MÍNIMA
            if ($request->has('capacidade_minima')) {
                $query->where('capacidade', '>=', $request->capacidade_minima);
            }

            // FILTROS DE INFRAESTRUTURA
            if ($request->has('acessibilidade')) {
                $query->where('acessibilidade', $request->boolean('acessibilidade'));
            }

            if ($request->has('ar_condicionado')) {
                $query->where('ar_condicionado', $request->boolean('ar_condicionado'));
            }

            if ($request->has('projetor')) {
                $query->where('projetor', $request->boolean('projetor'));
            }

            if ($request->has('computadores')) {
                $query->where('computadores', $request->boolean('computadores'));
            }

            // BUSCA POR NOME/CÓDIGO
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
                });
            }

            $sortField = $request->get('sort_field', 'codigo');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $perPage = $request->get('per_page', 15);
            
            if ($request->has('all') && $request->boolean('all')) {
                return response()->json($query->get());
            }

            return response()->json($query->paginate($perPage));

        } catch (\Exception $e) {
            Log::error('Erro ao listar espaços físicos: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao listar espaços físicos', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $espaco = EspacoFisico::with([
                'andar.bloco.predio.campus.instituicao',
                'responsavel.usuario',
                'reservas.solicitante',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return response()->json($espaco);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Espaço físico não encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'andar_id' => 'required|exists:andares,id',
                'codigo' => 'required|string|max:50',
                'nome' => 'required|string|max:255',
                'tipo' => 'required|in:Sala de Aula,Laboratório,Auditório,Biblioteca,Sala de Reunião,Sala de Professores,Coordenação,Diretoria,Secretaria,Almoxarifado,Banheiro,Copa/Cozinha,Área de Convivência,Estacionamento,Quadra Esportiva,Ginásio,Cantina,Outro',
                'area' => 'nullable|numeric|min:0',
                'capacidade' => 'nullable|integer|min:0',
                'capacidade_exame' => 'nullable|integer|min:0',
                'ar_condicionado' => 'boolean',
                'projetor' => 'boolean',
                'lousa_digital' => 'boolean',
                'computadores' => 'boolean',
                'quantidade_computadores' => 'nullable|integer|min:0',
                'wifi' => 'boolean',
                'acessibilidade' => 'boolean',
                'cameras_seguranca' => 'boolean',
                'sistema_som' => 'boolean',
                'quantidade_carteiras' => 'nullable|integer|min:0',
                'quantidade_cadeiras' => 'nullable|integer|min:0',
                'quantidade_mesas' => 'nullable|integer|min:0',
                'tipo_mobiliario' => 'nullable|string|max:255',
                'status' => 'required|in:Disponível,Ocupado,Manutenção,Reforma,Indisponível',
                'permite_reserva' => 'boolean',
                'horarios_disponiveis' => 'nullable|array',
                'responsavel_id' => 'nullable|exists:colaboradores,id',
                'fotos' => 'nullable|array',
                'videos_360' => 'nullable|array',
                'documentos' => 'nullable|array',
                'equipamentos' => 'nullable|array',
                'observacoes' => 'nullable|string',
                'restricoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do código por andar
            $exists = EspacoFisico::where('andar_id', $request->andar_id)
                                  ->where('codigo', $request->codigo)
                                  ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um espaço com este código neste andar'], 422);
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $espaco = EspacoFisico::create($data);

            return response()->json([
                'message' => 'Espaço físico criado com sucesso',
                'data' => $espaco->load(['andar.bloco.predio', 'responsavel'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar espaço físico: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar espaço físico', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $espaco = EspacoFisico::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'andar_id' => 'required|exists:andares,id',
                'codigo' => 'required|string|max:50',
                'nome' => 'required|string|max:255',
                'tipo' => 'required|in:Sala de Aula,Laboratório,Auditório,Biblioteca,Sala de Reunião,Sala de Professores,Coordenação,Diretoria,Secretaria,Almoxarifado,Banheiro,Copa/Cozinha,Área de Convivência,Estacionamento,Quadra Esportiva,Ginásio,Cantina,Outro',
                'area' => 'nullable|numeric|min:0',
                'capacidade' => 'nullable|integer|min:0',
                'capacidade_exame' => 'nullable|integer|min:0',
                'ar_condicionado' => 'boolean',
                'projetor' => 'boolean',
                'lousa_digital' => 'boolean',
                'computadores' => 'boolean',
                'quantidade_computadores' => 'nullable|integer|min:0',
                'wifi' => 'boolean',
                'acessibilidade' => 'boolean',
                'cameras_seguranca' => 'boolean',
                'sistema_som' => 'boolean',
                'quantidade_carteiras' => 'nullable|integer|min:0',
                'quantidade_cadeiras' => 'nullable|integer|min:0',
                'quantidade_mesas' => 'nullable|integer|min:0',
                'tipo_mobiliario' => 'nullable|string|max:255',
                'status' => 'required|in:Disponível,Ocupado,Manutenção,Reforma,Indisponível',
                'permite_reserva' => 'boolean',
                'horarios_disponiveis' => 'nullable|array',
                'responsavel_id' => 'nullable|exists:colaboradores,id',
                'fotos' => 'nullable|array',
                'videos_360' => 'nullable|array',
                'documentos' => 'nullable|array',
                'equipamentos' => 'nullable|array',
                'observacoes' => 'nullable|string',
                'restricoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do código por andar
            $exists = EspacoFisico::where('andar_id', $request->andar_id)
                                  ->where('codigo', $request->codigo)
                                  ->where('id', '!=', $id)
                                  ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um espaço com este código neste andar'], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            // Remove o campo 'created_by' para evitar que ele seja atualizado
            unset($data['created_by']);

            $espaco->update($data);

            return response()->json([
                'message' => 'Espaço físico atualizado com sucesso',
                'data' => $espaco->load(['andar.bloco.predio', 'responsavel'])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar espaço físico: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao atualizar espaço físico', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $espaco = EspacoFisico::findOrFail($id);

            // Verifica se há reservas ativas
            $reservasAtivas = $espaco->reservas()
                ->whereIn('status', ['Pendente', 'Aprovada'])
                ->where('data_fim', '>=', now())
                ->count();

            if ($reservasAtivas > 0) {
                return response()->json(['message' => 'Não é possível excluir um espaço com reservas ativas'], 422);
            }

            $espaco->delete();

            return response()->json(['message' => 'Espaço físico excluído com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir espaço físico: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir espaço físico', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica disponibilidade de um espaço
     */
    public function verificarDisponibilidade(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $espaco = EspacoFisico::findOrFail($id);

            // Verifica conflitos de reserva
            $conflitos = $espaco->reservas()
                ->where('status', 'Aprovada')
                ->where(function($q) use ($request) {
                    $q->whereBetween('data_inicio', [$request->data_inicio, $request->data_fim])
                      ->orWhereBetween('data_fim', [$request->data_inicio, $request->data_fim])
                      ->orWhere(function($q2) use ($request) {
                          $q2->where('data_inicio', '<=', $request->data_inicio)
                             ->where('data_fim', '>=', $request->data_fim);
                      });
                })
                ->get();

            $disponivel = $conflitos->isEmpty();

            return response()->json([
                'disponivel' => $disponivel,
                'conflitos' => $conflitos
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao verificar disponibilidade'], 500);
        }
    }

    /**
     * Estatísticas de espaços físicos
     */
    public function estatisticas(Request $request)
    {
        try {
            $stats = [
                'total' => EspacoFisico::count(),
                'disponiveis' => EspacoFisico::where('status', 'Disponível')->count(),
                'ocupados' => EspacoFisico::where('status', 'Ocupado')->count(),
                'manutencao' => EspacoFisico::where('status', 'Manutenção')->count(),
                'por_tipo' => EspacoFisico::select('tipo', DB::raw('count(*) as total'))
                    ->groupBy('tipo')
                    ->get(),
                'capacidade_total' => EspacoFisico::sum('capacidade'),
                'com_acessibilidade' => EspacoFisico::where('acessibilidade', true)->count(),
                'com_projetor' => EspacoFisico::where('projetor', true)->count(),
                'com_ar_condicionado' => EspacoFisico::where('ar_condicionado', true)->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao buscar estatísticas'], 500);
        }
    }

    /**
     * ✅ Exportar relatório de espaços físicos em PDF
     */
    /**
 * ✅ Exportar relatório de espaços físicos em PDF
 */
public function exportarPdf(Request $request)
{
    try {
        $query = EspacoFisico::with([
            'andar.bloco.predio.campus.instituicao.identidadeVisual',
            'andar.bloco.predio.campus',
            'andar.bloco.predio',
            'andar.bloco',
            'andar'
        ]);

        // ✅ APLICAR OS MESMOS FILTROS DA LISTAGEM
        if ($request->has('instituicao_id') && $request->instituicao_id) {
            $query->whereHas('andar.bloco.predio.campus.instituicao', function($q) use ($request) {
                $q->where('id', $request->instituicao_id);
            });
        }

        if ($request->has('campus_id') && $request->campus_id) {
            $query->whereHas('andar.bloco.predio.campus', function($q) use ($request) {
                $q->where('id', $request->campus_id);
            });
        }

        if ($request->has('predio_id') && $request->predio_id) {
            $query->whereHas('andar.bloco.predio', function($q) use ($request) {
                $q->where('id', $request->predio_id);
            });
        }

        if ($request->has('bloco_id') && $request->bloco_id) {
            $query->whereHas('andar.bloco', function($q) use ($request) {
                $q->where('id', $request->bloco_id);
            });
        }

        if ($request->has('andar_id') && $request->andar_id) {
            $query->where('andar_id', $request->andar_id);
        }

        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
                  ->orWhere('descricao', 'ilike', "%{$search}%");
            });
        }

        $espacos = $query->orderBy('codigo')->get();

        // ✅ BUSCAR IDENTIDADE VISUAL DA INSTITUIÇÃO FILTRADA
        $identidadeVisual = null;
        $instituicao = null;

        if ($request->has('instituicao_id') && $request->instituicao_id) {
            $instituicao = \App\Models\Instituicao::with('identidadeVisual')->find($request->instituicao_id);
            $identidadeVisual = $instituicao?->identidadeVisual;
        } else if ($espacos->isNotEmpty()) {
            $instituicao = $espacos->first()->andar?->bloco?->predio?->campus?->instituicao;
            $identidadeVisual = $instituicao?->identidadeVisual;
        }

        // ✅ SE AINDA NÃO TEM, BUSCAR DIRETAMENTE
        if (!$identidadeVisual) {
            $identidadeVisual = \App\Models\IdentidadeVisual::where('entidade_type', 'App\\Models\\Instituicao')
                ->where('entidade_id', $request->instituicao_id ?? $instituicao?->id)
                ->first();
        }

        // ✅ CONVERTER LOGO PARA BASE64 (se existir)
        $logoBase64 = null;
        if ($identidadeVisual) {
            // ✅ PRIORIZAR logo_principal, depois logo_horizontal, depois logo_icone
            $logoField = $identidadeVisual->logo_principal 
                ?? $identidadeVisual->logo_horizontal 
                ?? $identidadeVisual->logo_icone 
                ?? $identidadeVisual->logo; // Fallback para campo antigo
            
            if ($logoField) {
                $logoPath = storage_path('app/public/' . $logoField);
                
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
                } else {
                    Log::warning('Logo não encontrado', [
                        'path' => $logoPath,
                        'logo_field' => $logoField,
                        'identidade_visual_id' => $identidadeVisual->id
                    ]);
                }
            }
        }

        $data = [
            'espacos' => $espacos,
            'instituicao' => $instituicao,
            'identidadeVisual' => $identidadeVisual,
            'logoBase64' => $logoBase64,
            'dataGeracao' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name,
            'filtros' => [
                'instituicao' => $instituicao?->nome_fantasia ?? $instituicao?->razao_social,
                'campus' => $request->campus_id ? \App\Models\Campus::find($request->campus_id)?->nome : null,
                'predio' => $request->predio_id ? \App\Models\Predio::find($request->predio_id)?->nome : null,
                'bloco' => $request->bloco_id ? \App\Models\Bloco::find($request->bloco_id)?->nome : null,
                'andar' => $request->andar_id ? \App\Models\Andar::find($request->andar_id)?->nome : null,
                'tipo' => $request->tipo,
                'status' => $request->status,
            ]
        ];

        $pdf = Pdf::loadView('relatorios.espacos-fisicos', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('relatorio-espacos-fisicos-' . now()->format('Y-m-d-His') . '.pdf');

    } catch (\Exception $e) {
        Log::error('Erro ao gerar relatório PDF: ' . $e->getMessage());
        return response()->json([
            'message' => 'Erro ao gerar relatório',
            'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
        ], 500);
    }
}


    /**
     * ✅ Exportar relatório de espaços físicos em Excel
     */
    public function exportarExcel(Request $request)
    {
        // TODO: Implementar a lógica de exportação para Excel
        return response()->json(['message' => 'Funcionalidade ainda não implementada.'], 501);
    }
}
