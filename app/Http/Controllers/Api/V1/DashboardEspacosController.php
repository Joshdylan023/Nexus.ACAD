<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EspacoFisico;
use App\Models\ReservaEspaco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardEspacosController extends Controller
{
    /**
     * Dashboard 360° - Estatísticas Completas
     * ✅ ADICIONADO: Filtro de instituição
     */
    public function index(Request $request)
    {
        try {
            // ✅ ADICIONADO: Captura instituicao_id
            $instituicaoId = $request->input('instituicao_id');
            $campusId = $request->input('campus_id');
            $predioId = $request->input('predio_id');
            $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // ✅ LOG PARA DEBUG
            \Log::info('📊 Dashboard Espaços - Filtros:', [
                'instituicao_id' => $instituicaoId,
                'campus_id' => $campusId,
                'predio_id' => $predioId,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
            ]);

            return response()->json([
                'resumo' => $this->getResumo($instituicaoId, $campusId, $predioId),
                'ocupacao_tempo_real' => $this->getOcupacaoTempoReal($instituicaoId, $campusId, $predioId),
                'ocupacao_por_tipo' => $this->getOcupacaoPorTipo($dataInicio, $dataFim, $instituicaoId, $campusId, $predioId),
                'reservas_por_periodo' => $this->getReservasPorPeriodo($dataInicio, $dataFim, $instituicaoId, $campusId, $predioId),
                'top_espacos' => $this->getTopEspacos($dataInicio, $dataFim, $instituicaoId, $campusId, $predioId),
                'distribuicao_semanal' => $this->getDistribuicaoSemanal($dataInicio, $dataFim, $instituicaoId, $campusId, $predioId),
                'sugestoes_otimizacao' => $this->getSugestoesOtimizacao($instituicaoId, $campusId, $predioId),
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Erro no DashboardEspacosController: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erro ao carregar dashboard',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor',
                'resumo' => [
                    'total_espacos' => 0,
                    'espacos_disponiveis' => 0,
                    'espacos_ocupados' => 0,
                    'reservas_ativas' => 0,
                    'taxa_ocupacao' => 0
                ],
                'ocupacao_tempo_real' => [],
                'ocupacao_por_tipo' => [],
                'reservas_por_periodo' => [],
                'top_espacos' => [],
                'distribuicao_semanal' => [],
                'sugestoes_otimizacao' => []
            ], 500);
        }
    }

    /**
     * Resumo Geral
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getResumo($instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $query = EspacoFisico::query();

            // ✅ FILTRO POR INSTITUIÇÃO
            if ($instituicaoId) {
                $query->whereHas('andar.bloco.predio.campus', function($q) use ($instituicaoId) {
                    $q->where('instituicao_id', $instituicaoId);
                });
            }

            // FILTRO POR CAMPUS
            if ($campusId) {
                $query->whereHas('andar.bloco.predio', function($q) use ($campusId) {
                    $q->where('campus_id', $campusId);
                });
            }

            // FILTRO POR PRÉDIO
            if ($predioId) {
                $query->whereHas('andar.bloco', function($q) use ($predioId) {
                    $q->where('predio_id', $predioId);
                });
            }

            $totalEspacos = $query->count();
            $disponiveis = (clone $query)->where('status', 'Disponível')->count();
            $ocupados = (clone $query)->where('status', 'Ocupado')->count();
            $manutencao = (clone $query)->where('status', 'Manutenção')->count();

            $capacidadeTotal = (clone $query)->sum('capacidade');
            $comArCondicionado = (clone $query)->where('ar_condicionado', true)->count();
            $comProjetor = (clone $query)->where('projetor', true)->count();
            $comAcessibilidade = (clone $query)->where('acessibilidade', true)->count();

            $porTipo = (clone $query)->select('tipo', DB::raw('COUNT(*) as total'))
                ->groupBy('tipo')
                ->orderBy('total', 'desc')
                ->get();

            $reservasAtivas = ReservaEspaco::whereIn('status', ['Pendente', 'Aprovada'])
                ->where('data_fim', '>=', Carbon::now())
                ->count();

            $taxaOcupacao = $totalEspacos > 0 ? round((($totalEspacos - $disponiveis) / $totalEspacos) * 100, 2) : 0;

            return [
                'total_espacos' => $totalEspacos,
                'espacos_disponiveis' => $disponiveis,
                'espacos_ocupados' => $ocupados,
                'reservas_ativas' => $reservasAtivas,
                'taxa_ocupacao' => $taxaOcupacao,
                'manutencao' => $manutencao,
                'capacidade_total' => $capacidadeTotal,
                'com_ar_condicionado' => $comArCondicionado,
                'com_projetor' => $comProjetor,
                'com_acessibilidade' => $comAcessibilidade,
                'por_tipo' => $porTipo,
            ];
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getResumo: ' . $e->getMessage());
            return [
                'total_espacos' => 0,
                'espacos_disponiveis' => 0,
                'espacos_ocupados' => 0,
                'reservas_ativas' => 0,
                'taxa_ocupacao' => 0,
                'manutencao' => 0,
                'capacidade_total' => 0,
                'com_ar_condicionado' => 0,
                'com_projetor' => 0,
                'com_acessibilidade' => 0,
                'por_tipo' => [],
            ];
        }
    }

    /**
     * Ocupação em Tempo Real
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getOcupacaoTempoReal($instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $hoje = Carbon::now()->format('Y-m-d');
            $agora = Carbon::now()->format('H:i:s');

            $query = ReservaEspaco::where('data_inicio', '<=', $hoje)
                ->where('data_fim', '>=', $hoje)
                ->where('hora_inicio', '<=', $agora)
                ->where('hora_fim', '>=', $agora)
                ->where('status', 'Aprovada');

            if ($instituicaoId || $campusId || $predioId) {
                $query->whereHas('espacoFisico.andar.bloco.predio', function($q) use ($instituicaoId, $campusId, $predioId) {
                    if ($instituicaoId) {
                        $q->whereHas('campus', function($q2) use ($instituicaoId) {
                            $q2->where('instituicao_id', $instituicaoId);
                        });
                    }
                    if ($campusId) {
                        $q->where('campus_id', $campusId);
                    }
                    if ($predioId) {
                        $q->where('id', $predioId);
                    }
                });
            }

            return [
                'reservas_ativas_agora' => $query->count(),
                'proximas_2h' => $this->getProximasReservas(2, $instituicaoId, $campusId, $predioId),
            ];
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getOcupacaoTempoReal: ' . $e->getMessage());
            return [
                'reservas_ativas_agora' => 0,
                'proximas_2h' => 0
            ];
        }
    }

    /**
     * Próximas Reservas
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getProximasReservas($horas = 2, $instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $agora = Carbon::now();
            $limite = $agora->copy()->addHours($horas);

            $query = ReservaEspaco::where('data_inicio', $agora->format('Y-m-d'))
                ->where('hora_inicio', '>=', $agora->format('H:i:s'))
                ->where('hora_inicio', '<=', $limite->format('H:i:s'))
                ->where('status', 'Aprovada');

            if ($instituicaoId || $campusId || $predioId) {
                $query->whereHas('espacoFisico.andar.bloco.predio', function($q) use ($instituicaoId, $campusId, $predioId) {
                    if ($instituicaoId) {
                        $q->whereHas('campus', function($q2) use ($instituicaoId) {
                            $q2->where('instituicao_id', $instituicaoId);
                        });
                    }
                    if ($campusId) {
                        $q->where('campus_id', $campusId);
                    }
                    if ($predioId) {
                        $q->where('id', $predioId);
                    }
                });
            }

            return $query->count();
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getProximasReservas: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ocupação por Tipo
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getOcupacaoPorTipo($dataInicio, $dataFim, $instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $query = DB::table('espacos_fisicos as ef')
                ->select(
                    'ef.tipo',
                    DB::raw('COUNT(DISTINCT ef.id) as total_espacos'),
                    DB::raw('COALESCE(COUNT(DISTINCT re.id), 0) as total_reservas')
                )
                ->leftJoin('reservas_espacos as re', function($join) use ($dataInicio, $dataFim) {
                    $join->on('ef.id', '=', 're.espaco_fisico_id')
                        ->whereBetween('re.data_inicio', [$dataInicio, $dataFim])
                        ->whereIn('re.status', ['Aprovada', 'Concluída']);
                });

            // ✅ APLICAR FILTROS HIERÁRQUICOS
            if ($instituicaoId || $campusId || $predioId) {
                $query->join('andares as a', 'ef.andar_id', '=', 'a.id')
                    ->join('blocos as b', 'a.bloco_id', '=', 'b.id')
                    ->join('predios as p', 'b.predio_id', '=', 'p.id');

                if ($instituicaoId) {
                    $query->join('campi as c', 'p.campus_id', '=', 'c.id')
                        ->where('c.instituicao_id', $instituicaoId);
                } elseif ($campusId) {
                    $query->where('p.campus_id', $campusId);
                }

                if ($predioId) {
                    $query->where('p.id', $predioId);
                }
            }

            return $query->groupBy('ef.tipo')->get();
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getOcupacaoPorTipo: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reservas por Período
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getReservasPorPeriodo($dataInicio, $dataFim, $instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $query = DB::table('reservas_espacos as re')
                ->select(
                    DB::raw('DATE(re.data_inicio) as data'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('re.data_inicio', [$dataInicio, $dataFim])
                ->whereIn('re.status', ['Aprovada', 'Concluída']);

            if ($instituicaoId || $campusId || $predioId) {
                $query->join('espacos_fisicos as ef', 're.espaco_fisico_id', '=', 'ef.id')
                    ->join('andares as a', 'ef.andar_id', '=', 'a.id')
                    ->join('blocos as b', 'a.bloco_id', '=', 'b.id')
                    ->join('predios as p', 'b.predio_id', '=', 'p.id');

                if ($instituicaoId) {
                    $query->join('campi as c', 'p.campus_id', '=', 'c.id')
                        ->where('c.instituicao_id', $instituicaoId);
                } elseif ($campusId) {
                    $query->where('p.campus_id', $campusId);
                }

                if ($predioId) {
                    $query->where('p.id', $predioId);
                }
            }

            return $query->groupBy('data')->orderBy('data')->get();
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getReservasPorPeriodo: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Top Espaços
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getTopEspacos($dataInicio, $dataFim, $instituicaoId = null, $campusId = null, $predioId = null, $limit = 5)
    {
        try {
            $query = DB::table('espacos_fisicos as ef')
                ->select(
                    'ef.id',
                    'ef.codigo',
                    'ef.nome',
                    'ef.tipo',
                    DB::raw('COUNT(re.id) as total_reservas')
                )
                ->join('reservas_espacos as re', 'ef.id', '=', 're.espaco_fisico_id')
                ->whereBetween('re.data_inicio', [$dataInicio, $dataFim])
                ->whereIn('re.status', ['Aprovada', 'Concluída']);

            if ($instituicaoId || $campusId || $predioId) {
                $query->join('andares as a', 'ef.andar_id', '=', 'a.id')
                    ->join('blocos as b', 'a.bloco_id', '=', 'b.id')
                    ->join('predios as p', 'b.predio_id', '=', 'p.id');

                if ($instituicaoId) {
                    $query->join('campi as c', 'p.campus_id', '=', 'c.id')
                        ->where('c.instituicao_id', $instituicaoId);
                } elseif ($campusId) {
                    $query->where('p.campus_id', $campusId);
                }

                if ($predioId) {
                    $query->where('p.id', $predioId);
                }
            }

            return $query->groupBy('ef.id', 'ef.codigo', 'ef.nome', 'ef.tipo')
                ->orderBy('total_reservas', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getTopEspacos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Distribuição Semanal
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getDistribuicaoSemanal($dataInicio, $dataFim, $instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $query = DB::table('reservas_espacos as re')
                ->select(
                    DB::raw('EXTRACT(DOW FROM re.data_inicio) as dia_semana'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('re.data_inicio', [$dataInicio, $dataFim])
                ->whereIn('re.status', ['Aprovada', 'Concluída']);

            if ($instituicaoId || $campusId || $predioId) {
                $query->join('espacos_fisicos as ef', 're.espaco_fisico_id', '=', 'ef.id')
                    ->join('andares as a', 'ef.andar_id', '=', 'a.id')
                    ->join('blocos as b', 'a.bloco_id', '=', 'b.id')
                    ->join('predios as p', 'b.predio_id', '=', 'p.id');

                if ($instituicaoId) {
                    $query->join('campi as c', 'p.campus_id', '=', 'c.id')
                        ->where('c.instituicao_id', $instituicaoId);
                } elseif ($campusId) {
                    $query->where('p.campus_id', $campusId);
                }

                if ($predioId) {
                    $query->where('p.id', $predioId);
                }
            }

            $dados = $query->groupBy('dia_semana')->orderBy('dia_semana')->get();

            $diasSemana = [
                0 => 'Domingo',
                1 => 'Segunda',
                2 => 'Terça',
                3 => 'Quarta',
                4 => 'Quinta',
                5 => 'Sexta',
                6 => 'Sábado'
            ];

            return $dados->map(function($item) use ($diasSemana) {
                return [
                    'dia' => $diasSemana[$item->dia_semana] ?? 'Desconhecido',
                    'total' => $item->total
                ];
            });
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getDistribuicaoSemanal: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sugestões de Otimização
     * ✅ ADICIONADO: $instituicaoId
     */
    private function getSugestoesOtimizacao($instituicaoId = null, $campusId = null, $predioId = null)
    {
        try {
            $resumo = $this->getResumo($instituicaoId, $campusId, $predioId);
            $sugestoes = [];

            $taxaOcupacao = $resumo['total_espacos'] > 0 
                ? round(($resumo['espacos_ocupados'] / $resumo['total_espacos']) * 100, 2)
                : 0;

            if ($taxaOcupacao > 80) {
                $sugestoes[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Taxa de ocupação alta',
                    'mensagem' => "Taxa de {$taxaOcupacao}% - Considere aumentar o número de espaços disponíveis"
                ];
            }

            $percentualAcessibilidade = $resumo['total_espacos'] > 0 
                ? round(($resumo['com_acessibilidade'] / $resumo['total_espacos']) * 100, 2)
                : 0;

            if ($percentualAcessibilidade < 30 && $resumo['total_espacos'] > 0) {
                $sugestoes[] = [
                    'tipo' => 'info',
                    'titulo' => 'Baixa acessibilidade',
                    'mensagem' => "Apenas {$percentualAcessibilidade}% dos espaços possuem acessibilidade"
                ];
            }

            if ($resumo['manutencao'] > 0) {
                $sugestoes[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Espaços em manutenção',
                    'mensagem' => "{$resumo['manutencao']} espaços precisam de atenção"
                ];
            }

            if (empty($sugestoes) && $taxaOcupacao < 60) {
                $sugestoes[] = [
                    'tipo' => 'success',
                    'titulo' => 'Infraestrutura adequada',
                    'mensagem' => 'Taxa de ocupação em níveis saudáveis'
                ];
            }

            return $sugestoes;
        } catch (\Exception $e) {
            \Log::error('❌ Erro em getSugestoesOtimizacao: ' . $e->getMessage());
            return [];
        }
    }
}
