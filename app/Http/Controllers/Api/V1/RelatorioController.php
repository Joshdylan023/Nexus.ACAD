<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardRHExport;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Dashboard de RH com KPIs e gráficos
     */
    public function dashboardRH(Request $request): JsonResponse
    {
        try {
            $periodo = $request->get('periodo', 'mes');
            $datas = $this->calcularPeriodo($periodo, $request);
            
            // Query base
            $query = Colaborador::with([
                'usuario', 
                'setorVinculo.setor',
                'unidadeLotacao',
                'unidadeOrganizacional'
            ]);
            
            // Aplicar filtros hierárquicos
            $this->aplicarFiltrosHierarquicos($query, $request);
            
            // KPIs e dados
            $kpis = $this->calcularKPIs($query, $datas, $request);
            $evolucao = $this->calcularEvolucao($datas, $request);
            $statusDistribuicao = $this->calcularDistribuicaoStatus($request);
            $topSetores = $this->calcularTopSetores(10, $request);
            $topCargos = $this->calcularTopCargos(10, $request);
            $movimentacoes = $this->calcularMovimentacoes(10, $request);
            
            return response()->json([
                'kpis' => $kpis,
                'evolucao' => $evolucao,
                'statusDistribuicao' => $statusDistribuicao,
                'topSetores' => $topSetores,
                'topCargos' => $topCargos,
                'movimentacoes' => $movimentacoes
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar dashboard RH: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Erro ao gerar dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ✅ CORRIGIDO - Aplicar filtros hierárquicos usando unidade_organizacional e unidade_lotacao
     */
    private function aplicarFiltrosHierarquicos($query, $request)
    {
        // Filtro por status
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Filtro por tipo (gestor/colaborador)
        if ($request->has('tipo') && $request->get('tipo')) {
            $isGestor = $request->get('tipo') === 'gestor';
            $query->where('is_gestor', $isGestor);
        }
        
        // Filtro por cargo
        if ($request->has('cargo') && $request->get('cargo')) {
            $query->where('cargo', $request->get('cargo'));
        }
        
        // Filtro por setor específico
        if ($request->has('setor') && $request->get('setor')) {
            $query->whereHas('setorVinculo', function($q) use ($request) {
                $q->where('setor_id', $request->get('setor'));
            });
        }
        
        // ✅ FILTRO POR GRUPO EDUCACIONAL
        if ($request->has('grupoEducacional') && $request->get('grupoEducacional')) {
            $grupoId = $request->get('grupoEducacional');
            $query->where(function($q) use ($grupoId) {
                // Colaboradores da unidade organizacional = grupo_educacional
                $q->where(function($q2) use ($grupoId) {
                    $q2->where('unidade_organizacional_type', 'grupo_educacional')
                       ->where('unidade_organizacional_id', $grupoId);
                });
                
                // OU colaboradores de unidades de lotação que pertencem ao grupo
                $q->orWhere(function($q2) use ($grupoId) {
                    $q2->where('unidade_lotacao_type', 'grupo_educacional')
                       ->where('unidade_lotacao_id', $grupoId);
                });
            });
        }
        
        // ✅ FILTRO POR MANTENEDORA
        if ($request->has('mantenedora') && $request->get('mantenedora')) {
            $mantenedoraId = $request->get('mantenedora');
            $query->where(function($q) use ($mantenedoraId) {
                $q->where(function($q2) use ($mantenedoraId) {
                    $q2->where('unidade_organizacional_type', 'mantenedora')
                       ->where('unidade_organizacional_id', $mantenedoraId);
                });
                
                $q->orWhere(function($q2) use ($mantenedoraId) {
                    $q2->where('unidade_lotacao_type', 'mantenedora')
                       ->where('unidade_lotacao_id', $mantenedoraId);
                });
            });
        }
        
        // ✅ FILTRO POR INSTITUIÇÃO
        if ($request->has('instituicao') && $request->get('instituicao')) {
            $instituicaoId = $request->get('instituicao');
            $query->where(function($q) use ($instituicaoId) {
                $q->where(function($q2) use ($instituicaoId) {
                    $q2->where('unidade_organizacional_type', 'instituicao')
                       ->where('unidade_organizacional_id', $instituicaoId);
                });
                
                $q->orWhere(function($q2) use ($instituicaoId) {
                    $q2->where('unidade_lotacao_type', 'instituicao')
                       ->where('unidade_lotacao_id', $instituicaoId);
                });
            });
        }
        
        // ✅ FILTRO POR CAMPUS
        if ($request->has('campus') && $request->get('campus')) {
            $campusId = $request->get('campus');
            $query->where(function($q) use ($campusId) {
                $q->where(function($q2) use ($campusId) {
                    $q2->where('unidade_organizacional_type', 'campus')
                       ->where('unidade_organizacional_id', $campusId);
                });
                
                $q->orWhere(function($q2) use ($campusId) {
                    $q2->where('unidade_lotacao_type', 'campus')
                       ->where('unidade_lotacao_id', $campusId);
                });
            });
        }
    }
    
    /**
     * Calcular período com base no filtro
     */
    private function calcularPeriodo($periodo, $request)
    {
        $agora = Carbon::now();
        
        switch ($periodo) {
            case 'mes':
                return [
                    'inicio' => $agora->copy()->startOfMonth(),
                    'fim' => $agora->copy()->endOfMonth()
                ];
            case 'trimestre':
                return [
                    'inicio' => $agora->copy()->startOfQuarter(),
                    'fim' => $agora->copy()->endOfQuarter()
                ];
            case 'semestre':
                return [
                    'inicio' => $agora->copy()->subMonths(6),
                    'fim' => $agora
                ];
            case 'ano':
                return [
                    'inicio' => $agora->copy()->startOfYear(),
                    'fim' => $agora->copy()->endOfYear()
                ];
            case 'customizado':
                return [
                    'inicio' => Carbon::parse($request->get('dataInicio')),
                    'fim' => Carbon::parse($request->get('dataFim'))
                ];
            default:
                return [
                    'inicio' => $agora->copy()->startOfMonth(),
                    'fim' => $agora->copy()->endOfMonth()
                ];
        }
    }
    
    /**
     * Calcular KPIs principais
     */
    private function calcularKPIs($query, $datas, $request)
    {
        // Clonar a query base para diferentes cálculos
        $queryBase = clone $query;
        
        $total = $query->count();
        $ativos = (clone $queryBase)->where('status', 'Ativo')->count();
        $afastados = (clone $queryBase)->where('status', 'Afastado')->count();
        $desligados = (clone $queryBase)->where('status', 'Desligado')->count();
        $gestores = (clone $queryBase)->where('is_gestor', true)->where('status', 'Ativo')->count();
        
        // Admissões no período
        $queryAdmissoes = clone $queryBase;
        $admissoes = $queryAdmissoes->whereBetween('data_admissao', [
            $datas['inicio']->format('Y-m-d'),
            $datas['fim']->format('Y-m-d')
        ])->count();
        
        // Desligamentos no período
        $queryDesligamentos = clone $queryBase;
        $desligamentos = $queryDesligamentos->where('status', 'Desligado')
            ->whereNotNull('data_desligamento')
            ->whereBetween('data_desligamento', [
                $datas['inicio']->format('Y-m-d'),
                $datas['fim']->format('Y-m-d')
            ])->count();
        
        // Turnover
        $turnover = $ativos > 0 ? round(($desligamentos / $ativos) * 100, 2) : 0;
        
        // Tempo médio de casa
        $queryTempo = clone $queryBase;
        $colaboradoresAtivos = $queryTempo->where('status', 'Ativo')
            ->whereNotNull('data_admissao')
            ->get();
        
        $tempoMedio = $colaboradoresAtivos->avg(function($colaborador) {
            return Carbon::parse($colaborador->data_admissao)->diffInYears(Carbon::now());
        });
        
        // Aniversariantes do mês
        $mesAtual = Carbon::now()->month;
        $aniversariantes = User::whereMonth('data_nascimento', $mesAtual)->count();
        
        // Variação (comparar com mês anterior)
        $mesAnterior = Carbon::now()->subMonth();
        $queryVariacao = Colaborador::query();
        $this->aplicarFiltrosHierarquicos($queryVariacao, $request);
        $totalMesAnterior = $queryVariacao->where('created_at', '<', $mesAnterior->endOfMonth())->count();
        $variacao = $totalMesAnterior > 0 ? round((($total - $totalMesAnterior) / $totalMesAnterior) * 100, 2) : 0;
        
        // Total de setores
        $totalSetores = DB::table('setores')->count();
        
        return [
            'totalColaboradores' => $total,
            'ativos' => $ativos,
            'afastados' => $afastados,
            'desligados' => $desligados,
            'gestores' => $gestores,
            'admissoes' => $admissoes,
            'desligamentos' => $desligamentos,
            'turnover' => $turnover,
            'tempoMedio' => round($tempoMedio ?? 0, 1),
            'aniversariantes' => $aniversariantes,
            'totalSetores' => $totalSetores,
            'variacaoTotal' => $variacao
        ];
    }
    
    /**
     * Calcular evolução mensal
     */
    private function calcularEvolucao($datas, $request)
    {
        $meses = [];
        $admissoes = [];
        $desligamentos = [];
        
        $inicio = $datas['inicio']->copy();
        $fim = $datas['fim']->copy();
        
        // Limitar a 12 meses para não sobrecarregar
        $limiteIteracoes = 0;
        while ($inicio <= $fim && $limiteIteracoes < 12) {
            $mesNome = ucfirst($inicio->locale('pt_BR')->translatedFormat('M/y'));
            $meses[] = $mesNome;
            
            // Query com filtros aplicados
            $queryAdm = Colaborador::query();
            $this->aplicarFiltrosHierarquicos($queryAdm, $request);
            $adm = $queryAdm->whereYear('data_admissao', $inicio->year)
                ->whereMonth('data_admissao', $inicio->month)
                ->count();
            $admissoes[] = $adm;
            
            // Desligamentos do mês
            $queryDesl = Colaborador::query();
            $this->aplicarFiltrosHierarquicos($queryDesl, $request);
            $desl = $queryDesl->where('status', 'Desligado')
                ->whereNotNull('data_desligamento')
                ->whereYear('data_desligamento', $inicio->year)
                ->whereMonth('data_desligamento', $inicio->month)
                ->count();
            $desligamentos[] = $desl;
            
            $inicio->addMonth();
            $limiteIteracoes++;
        }
        
        return [
            'labels' => $meses,
            'admissoes' => $admissoes,
            'desligamentos' => $desligamentos
        ];
    }
    
    /**
     * Calcular distribuição por status
     */
    private function calcularDistribuicaoStatus($request)
    {
        $queryAtivos = Colaborador::query();
        $this->aplicarFiltrosHierarquicos($queryAtivos, $request);
        $ativos = $queryAtivos->where('status', 'Ativo')->count();
        
        $queryAfastados = Colaborador::query();
        $this->aplicarFiltrosHierarquicos($queryAfastados, $request);
        $afastados = $queryAfastados->where('status', 'Afastado')->count();
        
        $queryDesligados = Colaborador::query();
        $this->aplicarFiltrosHierarquicos($queryDesligados, $request);
        $desligados = $queryDesligados->where('status', 'Desligado')->count();
        
        return [
            'ativos' => $ativos,
            'afastados' => $afastados,
            'desligados' => $desligados
        ];
    }
    
    /**
     * Top setores com mais colaboradores
     */
    private function calcularTopSetores($limite, $request)
    {
        $query = Colaborador::with('setorVinculo.setor')
            ->where('status', 'Ativo');
        
        $this->aplicarFiltrosHierarquicos($query, $request);
        
        $setores = $query->get()
            ->groupBy(function($item) {
                return $item->setorVinculo?->setor?->nome ?? 'Sem Setor';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take($limite);
        
        return [
            'labels' => $setores->keys()->toArray(),
            'valores' => $setores->values()->toArray()
        ];
    }
    
    /**
     * Top cargos com mais colaboradores
     */
    private function calcularTopCargos($limite, $request)
    {
        $query = Colaborador::where('status', 'Ativo');
        $this->aplicarFiltrosHierarquicos($query, $request);
        
        $cargos = $query->select('cargo', DB::raw('count(*) as total'))
            ->groupBy('cargo')
            ->orderByDesc('total')
            ->limit($limite)
            ->get();
        
        return [
            'labels' => $cargos->pluck('cargo')->toArray(),
            'valores' => $cargos->pluck('total')->toArray()
        ];
    }
    
    /**
     * Movimentações recentes
     */
    private function calcularMovimentacoes($limite, $request)
    {
        $movimentacoes = collect();
        
        // Admissões recentes
        $queryAdm = Colaborador::with(['usuario', 'setorVinculo.setor']);
        $this->aplicarFiltrosHierarquicos($queryAdm, $request);
        $admissoes = $queryAdm->whereNotNull('data_admissao')
            ->orderByDesc('data_admissao')
            ->limit($limite)
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'data' => Carbon::parse($c->data_admissao)->format('d/m/Y'),
                    'nome' => $c->usuario->name,
                    'tipo' => 'Admissão',
                    'cargo' => $c->cargo,
                    'setor' => $c->setorVinculo?->setor?->nome ?? 'N/A'
                ];
            });
        
        // Desligamentos recentes
        $queryDesl = Colaborador::with(['usuario', 'setorVinculo.setor']);
        $this->aplicarFiltrosHierarquicos($queryDesl, $request);
        $desligamentos = $queryDesl->where('status', 'Desligado')
            ->whereNotNull('data_desligamento')
            ->orderByDesc('data_desligamento')
            ->limit($limite)
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'data' => Carbon::parse($c->data_desligamento)->format('d/m/Y'),
                    'nome' => $c->usuario->name,
                    'tipo' => 'Desligamento',
                    'cargo' => $c->cargo,
                    'setor' => $c->setorVinculo?->setor?->nome ?? 'N/A'
                ];
            });
        
        $movimentacoes = $admissoes->merge($desligamentos)
            ->sortByDesc('data')
            ->take($limite)
            ->values();
        
        return $movimentacoes;
    }
    
    /**
     * Exportar Dashboard de RH em Excel
     */
    public function exportarDashboard(Request $request)
    {
        try {
            $periodo = $request->get('periodo', 'mes');
            $datas = $this->calcularPeriodo($periodo, $request);
            
            // Coletar todos os dados
            $query = Colaborador::with(['usuario', 'setorVinculo.setor']);
            $this->aplicarFiltrosHierarquicos($query, $request);
            
            $kpis = $this->calcularKPIs($query, $datas, $request);
            $evolucao = $this->calcularEvolucao($datas, $request);
            $movimentacoes = $this->calcularMovimentacoes(50, $request);
            
            $dados = [
                'kpis' => $kpis,
                'evolucao' => $evolucao,
                'movimentacoes' => $movimentacoes,
                'periodo' => $periodo,
                'dataGeracao' => Carbon::now()->format('d/m/Y H:i:s')
            ];
            
            return Excel::download(
                new DashboardRHExport($dados), 
                'dashboard-rh-' . date('Y-m-d-His') . '.xlsx'
            );
            
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar dashboard: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao exportar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * ✅ Buscar colaboradores com informações pendentes
 */
public function colaboradoresPendentes(Request $request)
{
    try {
        $pendentes = Colaborador::with('usuario')->where(function($query) {
                $query->whereNull('unidade_organizacional_id')
                    ->orWhereNull('unidade_lotacao_id')
                    ->orWhereNull('setor_vinculo_id');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($colab) {
                $campos_pendentes = [];
                
                if (!$colab->unidade_organizacional_id) {
                    $campos_pendentes[] = 'Unidade Organizacional';
                }
                if (!$colab->unidade_lotacao_id) {
                    $campos_pendentes[] = 'Unidade de Lotação';
                }
                if (!$colab->setor_vinculo_id) {
                    $campos_pendentes[] = 'Setor';
                }
                
                return [
                    'id' => $colab->id,
                    'nome' => $colab->usuario->name,
                    'matricula' => $colab->matricula_funcional,
                    'cargo' => $colab->cargo,
                    'email' => $colab->email_funcional,
                    'data_admissao' => $colab->data_admissao ? Carbon::parse($colab->data_admissao)->format('d/m/Y') : null,
                    'importado_em' => $colab->synced_at ? Carbon::parse($colab->synced_at)->format('d/m/Y H:i') : null,
                    'campos_pendentes' => $campos_pendentes,
                    'total_pendencias' => count($campos_pendentes)
                ];
            });
        
        return response()->json([
            'total' => $pendentes->count(),
            'colaboradores' => $pendentes
        ]);
        
    } catch (\Exception $e) {
        Log::error('Erro ao buscar colaboradores pendentes: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao buscar colaboradores pendentes',
            'message' => $e->getMessage()
        ], 500);
    }
}
}

