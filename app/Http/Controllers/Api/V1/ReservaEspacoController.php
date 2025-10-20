<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReservaEspaco;
use App\Models\EspacoFisico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ReservaAprovadaNotification;
use App\Notifications\ReservaRejeitadaNotification;
use App\Notifications\NovaReservaPendenteNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Traits\ExportaPdfTrait;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservaEspacoController extends Controller
{
    use ExportaPdfTrait;

    public function index(Request $request)
    {
        try {
            $query = ReservaEspaco::with([
                'espacoFisico.andar.bloco.predio.campus.instituicao',
                'solicitante',
                'aprovador',
                'createdBy',
                'updatedBy'
            ]);

            // ✅ FILTROS BÁSICOS
            if ($request->has('espaco_fisico_id') && $request->espaco_fisico_id) {
                $query->where('espaco_fisico_id', $request->espaco_fisico_id);
            }

            if ($request->has('solicitante_id')) {
                $query->where('solicitante_id', $request->solicitante_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('data_inicio')) {
                $query->whereDate('data_inicio', '>=', $request->data_inicio);
            }

            if ($request->has('data_fim')) {
                $query->whereDate('data_fim', '<=', $request->data_fim);
            }

            if ($request->has('finalidade') && $request->finalidade) {
                $query->where('finalidade', $request->finalidade);
            }

            // ✅ FILTROS HIERÁRQUICOS
            if ($request->has('instituicao_id') && $request->instituicao_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio.campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->has('campus_id') && $request->campus_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio.campus', function($q) use ($request) {
                    $q->where('id', $request->campus_id);
                });
            }

            if ($request->has('predio_id') && $request->predio_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio', function($q) use ($request) {
                    $q->where('id', $request->predio_id);
                });
            }

            if ($request->has('bloco_id') && $request->bloco_id) {
                $query->whereHas('espacoFisico.andar.bloco', function($q) use ($request) {
                    $q->where('id', $request->bloco_id);
                });
            }

            if ($request->has('andar_id') && $request->andar_id) {
                $query->whereHas('espacoFisico.andar', function($q) use ($request) {
                    $q->where('id', $request->andar_id);
                });
            }

            if ($request->has('tipo_espaco') && $request->tipo_espaco) {
                $query->whereHas('espacoFisico', function($q) use ($request) {
                    $q->where('tipo', $request->tipo_espaco);
                });
            }

            // Minhas reservas
            if ($request->has('minhas') && $request->boolean('minhas')) {
                $query->where('solicitante_id', auth()->id());
            }

            // Reservas pendentes de aprovação
            if ($request->has('pendentes_aprovacao') && $request->boolean('pendentes_aprovacao')) {
                $query->where('status', 'Pendente');
            }

            // Busca textual
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('motivo', 'ilike', "%{$search}%")
                      ->orWhereHas('espacoFisico', function($q2) use ($search) {
                          $q2->where('nome', 'ilike', "%{$search}%")
                             ->orWhere('codigo', 'ilike', "%{$search}%");
                      });
                });
            }

            $sortField = $request->get('sort_field', 'data_inicio');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            $perPage = $request->get('per_page', 15);
            
            if ($request->has('all') && $request->boolean('all')) {
                return response()->json($query->get());
            }

            return response()->json($query->paginate($perPage));

        } catch (\Exception $e) {
            Log::error('Erro ao listar reservas: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao listar reservas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reserva = ReservaEspaco::with([
                'espacoFisico.andar.bloco.predio.campus.instituicao',
                'solicitante',
                'aprovador',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return response()->json($reserva);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Reserva não encontrada'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'espaco_fisico_id' => 'required|exists:espacos_fisicos,id',
                'data_inicio' => 'required|date|after_or_equal:today',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i',
                'recorrente' => 'boolean',
                'dias_semana' => 'nullable|array',
                'dias_semana.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
                'motivo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'quantidade_pessoas' => 'nullable|integer|min:1',
                'recursos_adicionais' => 'nullable|array',
                'finalidade' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica se o espaço existe e permite reserva
            $espaco = EspacoFisico::findOrFail($request->espaco_fisico_id);
            
            if (!$espaco->permite_reserva) {
                return response()->json(['message' => 'Este espaço não permite reservas'], 422);
            }

            if ($espaco->status !== 'Disponível') {
                return response()->json(['message' => 'Este espaço não está disponível'], 422);
            }

            // Verifica se há conflito de horário
            $conflito = ReservaEspaco::where('espaco_fisico_id', $request->espaco_fisico_id)
                ->where('status', '!=', 'Cancelada')
                ->where('status', '!=', 'Rejeitada')
                ->where(function($q) use ($request) {
                    $q->where(function($q2) use ($request) {
                        $q2->whereDate('data_inicio', '<=', $request->data_inicio)
                           ->whereDate('data_fim', '>=', $request->data_inicio);
                    })
                    ->orWhere(function($q2) use ($request) {
                        $q2->whereDate('data_inicio', '<=', $request->data_fim)
                           ->whereDate('data_fim', '>=', $request->data_fim);
                    })
                    ->orWhere(function($q2) use ($request) {
                        $q2->whereDate('data_inicio', '>=', $request->data_inicio)
                           ->whereDate('data_fim', '<=', $request->data_fim);
                    });
                })
                ->exists();

            if ($conflito) {
                return response()->json(['message' => 'Já existe uma reserva aprovada para este período'], 422);
            }

            $data = $request->all();
            $data['solicitante_id'] = auth()->id();
            $data['status'] = 'Pendente';
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $reserva = ReservaEspaco::create($data);

            // Notificar gestores
            $gestores = User::permission('aprovar-reservas')->get();
            Notification::send($gestores, new NovaReservaPendenteNotification($reserva));

            Log::info('Reserva criada', ['reserva_id' => $reserva->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Reserva solicitada com sucesso. Aguarde aprovação.',
                'data' => $reserva->load(['espacoFisico', 'solicitante'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reserva = ReservaEspaco::findOrFail($id);

            // Apenas o solicitante ou admin pode editar
            if ($reserva->solicitante_id !== auth()->id() && !auth()->user()->hasRole('Super Admin')) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }

            // Não pode editar reservas já aprovadas ou concluídas
            if (in_array($reserva->status, ['Aprovada', 'Concluída'])) {
                return response()->json(['message' => 'Não é possível editar uma reserva aprovada ou concluída'], 422);
            }

            $validator = Validator::make($request->all(), [
                'espaco_fisico_id' => 'required|exists:espacos_fisicos,id',
                'data_inicio' => 'required|date|after_or_equal:today',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i',
                'recorrente' => 'boolean',
                'dias_semana' => 'nullable|array',
                'motivo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'quantidade_pessoas' => 'nullable|integer|min:1',
                'recursos_adicionais' => 'nullable|array',
                'observacoes' => 'nullable|string',
                'finalidade' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = 'Pendente'; // Volta para pendente após edição
            $data['updated_by'] = auth()->id();

            $reserva->update($data);

            return response()->json([
                'message' => 'Reserva atualizada com sucesso',
                'data' => $reserva->load(['espacoFisico', 'solicitante'])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao atualizar reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reserva = ReservaEspaco::findOrFail($id);

            // Apenas o solicitante ou admin pode excluir
            if ($reserva->solicitante_id !== auth()->id() && !auth()->user()->hasRole('Super Admin')) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }

            $reserva->delete();

            return response()->json(['message' => 'Reserva excluída com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function aprovar(Request $request, $id)
    {
        try {
            $reserva = ReservaEspaco::findOrFail($id);

            if ($reserva->status !== 'Pendente') {
                return response()->json(['message' => 'Esta reserva não está pendente'], 422);
            }

            $reserva->update([
                'status' => 'Aprovada',
                'aprovado_por' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $reserva->solicitante->notify(new ReservaAprovadaNotification($reserva));

            Log::info('Reserva aprovada', ['reserva_id' => $reserva->id, 'aprovador' => auth()->id()]);

            return response()->json([
                'message' => 'Reserva aprovada com sucesso',
                'data' => $reserva->load(['espacoFisico', 'solicitante', 'aprovador'])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao aprovar reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao aprovar reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function rejeitar(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'motivo_rejeicao' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $reserva = ReservaEspaco::findOrFail($id);

            if ($reserva->status !== 'Pendente') {
                return response()->json(['message' => 'Esta reserva não está pendente'], 422);
            }

            $reserva->update([
                'status' => 'Rejeitada',
                'motivo_rejeicao' => $request->motivo_rejeicao,
                'aprovado_por' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $reserva->solicitante->notify(new ReservaRejeitadaNotification($reserva));

            Log::info('Reserva rejeitada', ['reserva_id' => $reserva->id, 'rejeitador' => auth()->id()]);

            return response()->json([
                'message' => 'Reserva rejeitada',
                'data' => $reserva
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao rejeitar reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function cancelar($id)
    {
        try {
            $reserva = ReservaEspaco::findOrFail($id);

            if ($reserva->solicitante_id !== auth()->id()) {
                return response()->json(['message' => 'Apenas o solicitante pode cancelar a reserva'], 403);
            }

            if (in_array($reserva->status, ['Cancelada', 'Concluída'])) {
                return response()->json(['message' => 'Esta reserva já foi cancelada ou concluída'], 422);
            }

            $reserva->update([
                'status' => 'Cancelada',
                'updated_by' => auth()->id(),
            ]);

            Log::info('Reserva cancelada', ['reserva_id' => $reserva->id, 'user_id' => auth()->id()]);

            return response()->json(['message' => 'Reserva cancelada com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar reserva: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao cancelar reserva', 'error' => $e->getMessage()], 500);
        }
    }

    public function estatisticas(Request $request)
    {
        try {
            $stats = [
                'total' => ReservaEspaco::count(),
                'pendentes' => ReservaEspaco::where('status', 'Pendente')->count(),
                'aprovadas' => ReservaEspaco::where('status', 'Aprovada')->count(),
                'rejeitadas' => ReservaEspaco::where('status', 'Rejeitada')->count(),
                'canceladas' => ReservaEspaco::where('status', 'Cancelada')->count(),
                'concluidas' => ReservaEspaco::where('status', 'Concluída')->count(),
                'proximas' => ReservaEspaco::where('status', 'Aprovada')
                    ->where('data_inicio', '>=', now())
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas de reservas: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao buscar estatísticas'], 500);
        }
    }

    /**
     * ✅ Dados para FullCalendar COM SUPORTE À RECORRÊNCIA
     */
    public function calendario(Request $request)
    {
        try {
            $dataInicio = $request->input('start');
            $dataFim = $request->input('end');

            $query = ReservaEspaco::with([
                'espacoFisico:id,nome,codigo,tipo,andar_id',
                'espacoFisico.andar.bloco.predio.campus.instituicao',
                'solicitante:id,name'
            ])
            ->whereIn('status', ['Pendente', 'Aprovada'])
            ->whereBetween('data_inicio', [$dataInicio, $dataFim]);

            // ✅ FILTROS HIERÁRQUICOS
            if ($request->has('instituicao_id') && $request->instituicao_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio.campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->has('campus_id') && $request->campus_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio.campus', function($q) use ($request) {
                    $q->where('id', $request->campus_id);
                });
            }

            if ($request->has('predio_id') && $request->predio_id) {
                $query->whereHas('espacoFisico.andar.bloco.predio', function($q) use ($request) {
                    $q->where('id', $request->predio_id);
                });
            }

            if ($request->has('bloco_id') && $request->bloco_id) {
                $query->whereHas('espacoFisico.andar.bloco', function($q) use ($request) {
                    $q->where('id', $request->bloco_id);
                });
            }

            if ($request->has('andar_id') && $request->andar_id) {
                $query->whereHas('espacoFisico.andar', function($q) use ($request) {
                    $q->where('id', $request->andar_id);
                });
            }

            if ($request->has('espaco_id') && $request->espaco_id) {
                $query->where('espaco_fisico_id', $request->espaco_id);
            }

            $reservas = $query->get();

            // ✅ FORMATAR PARA FULLCALENDAR COM SUPORTE À RECORRÊNCIA
            $eventos = collect();
            
            foreach ($reservas as $reserva) {
                $rawData = DB::table('reservas_espacos')
                    ->where('id', $reserva->id)
                    ->select('data_inicio', 'data_fim', 'hora_inicio', 'hora_fim', 'recorrente', 'dias_semana')
                    ->first();

                $eventoBase = [
                    'id' => $reserva->id,
                    'title' => $reserva->motivo . ' - ' . $reserva->espacoFisico->nome,
                    'backgroundColor' => $this->getEventColor($reserva->status),
                    'borderColor' => $this->getEventColor($reserva->status),
                    'extendedProps' => [
                        'status' => $reserva->status,
                        'espaco' => $reserva->espacoFisico->nome,
                        'tipo' => $reserva->espacoFisico->tipo,
                        'solicitante' => $reserva->solicitante->name,
                        'finalidade' => $reserva->finalidade,
                    ]
                ];

                // ✅ SE FOR RECORRENTE, GERAR MÚLTIPLAS OCORRÊNCIAS
                if ($rawData->recorrente && $rawData->dias_semana) {
                    $diasSemana = is_string($rawData->dias_semana) 
                        ? json_decode($rawData->dias_semana, true) 
                        : $rawData->dias_semana;

                    $diasMap = [
                        'dom' => 0,
                        'seg' => 1,
                        'ter' => 2,
                        'qua' => 3,
                        'qui' => 4,
                        'sex' => 5,
                        'sab' => 6
                    ];

                    $dataInicioCarbon = \Carbon\Carbon::parse($rawData->data_inicio);
                    $dataFimCarbon = \Carbon\Carbon::parse($rawData->data_fim);
                    $dataFimPeriodo = \Carbon\Carbon::parse($dataFim);

                    foreach ($diasSemana as $dia) {
                        $diaSemanaNum = $diasMap[$dia] ?? null;
                        if ($diaSemanaNum === null) continue;

                        $dataOcorrencia = $dataInicioCarbon->copy();
                        while ($dataOcorrencia->dayOfWeek != $diaSemanaNum && $dataOcorrencia->lte($dataFimCarbon)) {
                            $dataOcorrencia->addDay();
                        }

                        while ($dataOcorrencia->lte($dataFimCarbon) && $dataOcorrencia->lte($dataFimPeriodo)) {
                            $eventos->push(array_merge($eventoBase, [
                                'start' => $dataOcorrencia->format('Y-m-d') . 'T' . $rawData->hora_inicio,
                                'end' => $dataOcorrencia->format('Y-m-d') . 'T' . $rawData->hora_fim,
                            ]));

                            $dataOcorrencia->addWeek();
                        }
                    }
                } else {
                    // ✅ EVENTO ÚNICO
                    $eventos->push(array_merge($eventoBase, [
                        'start' => $rawData->data_inicio . 'T' . $rawData->hora_inicio,
                        'end' => $rawData->data_fim . 'T' . $rawData->hora_fim,
                    ]));
                }
            }

            return response()->json($eventos->values());

        } catch (\Exception $e) {
            Log::error('Erro ao carregar calendário: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Erro ao carregar calendário',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    private function getEventColor($status)
    {
        return match($status) {
            'Pendente' => '#fbbf24',
            'Aprovada' => '#10b981',
            'Rejeitada' => '#ef4444',
            'Cancelada' => '#6b7280',
            'Concluída' => '#3b82f6',
            default => '#6b7280'
        };
    }

    /**
     * Exportar relatório de reservas em PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            $query = ReservaEspaco::with([
                'espacoFisico.andar.bloco.predio.campus.instituicao',
                'solicitante',
                'aprovador',
            ]);

            // Filtros
            if ($request->filled('instituicao_id')) {
                $query->whereHas('espacoFisico.andar.bloco.predio.campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->filled('espaco_fisico_id')) {
                $query->where('espaco_fisico_id', $request->espaco_fisico_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('data_inicio')) {
                $query->whereDate('data_inicio', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->whereDate('data_fim', '<=', $request->data_fim);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('finalidade', 'ilike', "%{$search}%")
                      ->orWhere('observacoes', 'ilike', "%{$search}%")
                      ->orWhereHas('espacoFisico', function($q2) use ($search) {
                          $q2->where('nome', 'ilike', "%{$search}%");
                      });
                });
            }

            $reservas = $query->orderBy('data_inicio', 'desc')->get();

            [$identidadeVisual, $instituicao] = $this->buscarIdentidadeVisual(
                $request->instituicao_id,
                $reservas->first()
            );

            $logoBase64 = $this->converterLogoBase64($identidadeVisual);

            // Formatação do período
            $periodo = null;
            if ($request->filled('data_inicio') || $request->filled('data_fim')) {
                $inicio = $request->data_inicio ? \Carbon\Carbon::parse($request->data_inicio)->format('d/m/Y') : '';
                $fim = $request->data_fim ? \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') : '';
                
                if ($inicio && $fim) {
                    $periodo = "$inicio a $fim";
                } else if ($inicio) {
                    $periodo = "A partir de $inicio";
                } else if ($fim) {
                    $periodo = "Até $fim";
                }
            }

            $data = [
                'reservas' => $reservas,
                'instituicao' => $instituicao,
                'identidadeVisual' => $identidadeVisual,
                'logoBase64' => $logoBase64,
                'dataGeracao' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
                'filtros' => [
                    'instituicao' => $instituicao?->nome_fantasia ?? $instituicao?->razao_social,
                    'periodo' => $periodo,
                    'status' => $request->status ? ucfirst($request->status) : null,
                    'espaco' => $request->espaco_fisico_id ? \App\Models\EspacoFisico::find($request->espaco_fisico_id)?->nome : null,
                ]
            ];

            $pdf = Pdf::loadView('relatorios.reservas', $data);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('relatorio-reservas-' . now()->format('Y-m-d-His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório PDF de reservas: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar relatório',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}
