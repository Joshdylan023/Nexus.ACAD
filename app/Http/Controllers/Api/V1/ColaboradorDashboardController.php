<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Setor;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ColaboradorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $colaborador = $user->colaborador;

        return response()->json([
            'saudacao' => $this->getSaudacao($user),
            'info_colaborador' => $this->getInfoColaborador($user, $colaborador),
            'pendencias' => $this->getPendencias($user, $colaborador),
            'indicadores' => $this->getIndicadores($user),
            'acoes_rapidas' => $this->getAcoesRapidas($user),
            'atividades_recentes' => $this->getAtividadesRecentes($user),
            'widgets_contextuais' => $this->getWidgetsContextuais($user, $colaborador),
            'estatisticas_rapidas' => $this->getEstatisticasRapidas($user),
        ]);
    }

    private function getSaudacao($user)
    {
        $hora = Carbon::now()->hour;
        
        if ($hora >= 6 && $hora < 12) {
            $periodo = 'Bom dia';
        } elseif ($hora >= 12 && $hora < 18) {
            $periodo = 'Boa tarde';
        } else {
            $periodo = 'Boa noite';
        }

        return "{$periodo}, {$user->name}!";
    }

    private function getInfoColaborador($user, $colaborador)
    {
        if (!$colaborador) {
            return null;
        }

        return [
            'nome' => $user->name,
            'cargo' => $colaborador->cargo,
            'matricula' => $colaborador->matricula_funcional,
            'setor' => $colaborador->setorVinculo?->setor?->nome,
            'unidade_lotacao' => $this->getUnidadeLotacaoNome($colaborador),
            'is_gestor' => $colaborador->is_gestor,
            'foto' => $colaborador->foto_registro_rh ? "/storage/{$colaborador->foto_registro_rh}" : null,
            'data_admissao' => $colaborador->data_admissao ? Carbon::parse($colaborador->data_admissao)->format('d/m/Y') : null,
            'tempo_empresa' => $colaborador->data_admissao ? Carbon::parse($colaborador->data_admissao)->diffForHumans() : null,
        ];
    }

    private function getUnidadeLotacaoNome($colaborador)
    {
        $type = $colaborador->unidade_lotacao_type;
        $id = $colaborador->unidade_lotacao_id;

        if (!$type || !$id) return null;

        switch ($type) {
            case 'grupo_educacional':
                $unidade = GrupoEducacional::find($id);
                return $unidade?->nome;
            case 'mantenedora':
                $unidade = Mantenedora::find($id);
                return $unidade?->razao_social ?? $unidade?->nome_fantasia;
            case 'instituicao':
                $unidade = Instituicao::find($id);
                return $unidade?->nome_fantasia ?? $unidade?->razao_social;
            default:
                return null;
        }
    }

    private function getPendencias($user, $colaborador)
    {
        $pendencias = [
            'total' => 0,
            'criticas' => 0,
            'lista' => [],
        ];

        // Aniversariantes hoje
        $aniversariantesHoje = $this->getAniversariantesHoje();
        if ($aniversariantesHoje > 0) {
            $pendencias['lista'][] = [
                'tipo' => 'aniversario',
                'titulo' => '🎂 Aniversariantes Hoje!',
                'descricao' => "{$aniversariantesHoje} colaborador(es) fazem aniversário hoje",
                'urgencia' => 'media',
                'icone' => 'fas fa-birthday-cake',
                'link' => '/admin/rh/aniversariantes', // ⭐ ADICIONE ESSA LINHA
            ];
        }

        // TODO: Adicionar outras pendências reais aqui
        // Exemplos:
        // - Documentos pendentes para assinatura
        // - Aprovações necessárias (se gestor)
        // - Atos regulatórios vencendo
        // - Tarefas atribuídas
        // - Avaliações de desempenho pendentes

        $pendencias['total'] = count($pendencias['lista']);

        return $pendencias;
    }

    private function getIndicadores($user)
    {
        $indicadores = [];

        // Indicadores Institucionais
        if ($user->can('view institucional') || $user->hasRole('super-admin')) {
            $indicadores['institucional'] = [
                'grupos_educacionais' => GrupoEducacional::count(),
                'mantenedoras' => Mantenedora::count(),
                'instituicoes' => Instituicao::count(),
                'instituicoes_ativas' => Instituicao::where('status', 'Ativo')->count(),
                'campi' => Campus::count(),
                'setores' => Setor::count(),
            ];
        }

        // Indicadores RH
        if ($user->can('view rh') || $user->can('view colaboradores') || $user->hasRole('super-admin')) {
            $indicadores['rh'] = [
                'colaboradores_ativos' => Colaborador::where('status', 'Ativo')->count(),
                'colaboradores_afastados' => Colaborador::where('status', 'Afastado')->count(),
                'colaboradores_desligados' => Colaborador::where('status', 'Desligado')->count(),
                'aniversariantes_mes' => $this->getAniversariantesMes(),
                'aniversariantes_hoje' => $this->getAniversariantesHoje(),
                'gestores' => Colaborador::where('is_gestor', true)->where('status', 'Ativo')->count(),
            ];
        }

        return $indicadores;
    }

    private function getAcoesRapidas($user)
    {
        $acoes = [];

        // Sempre disponível
        $acoes[] = [
            'label' => 'Minha Área',
            'descricao' => 'Ver e editar meus dados',
            'icone' => 'fas fa-user-circle',
            'cor' => 'primary',
            'rota' => '/admin/minha-area',
        ];

        // Permissão de criar colaborador
        if ($user->can('create colaborador') || $user->hasRole('super-admin')) {
            $acoes[] = [
                'label' => 'Novo Colaborador',
                'descricao' => 'Cadastrar novo colaborador',
                'icone' => 'fas fa-user-plus',
                'cor' => 'success',
                'rota' => '/admin/pessoas-acessos/colaboradores/create',
            ];
        }

        // Permissão de criar instituição
        if ($user->can('create instituicao') || $user->hasRole('super-admin')) {
            $acoes[] = [
                'label' => 'Nova Instituição',
                'descricao' => 'Cadastrar nova instituição',
                'icone' => 'fas fa-building',
                'cor' => 'info',
                'rota' => '/admin/institucional/instituicoes/create',
            ];
        }

        // Permissão de criar campus
        if ($user->can('create campus') || $user->hasRole('super-admin')) {
            $acoes[] = [
                'label' => 'Novo Campus',
                'descricao' => 'Cadastrar novo campus',
                'icone' => 'fas fa-map-marker-alt',
                'cor' => 'warning',
                'rota' => '/admin/institucional/campi/create',
            ];
        }

        // Dashboard Institucional
        if ($user->can('view institucional') || $user->hasRole('super-admin')) {
            $acoes[] = [
                'label' => 'Dashboard Institucional',
                'descricao' => 'Visão geral do módulo',
                'icone' => 'fas fa-chart-line',
                'cor' => 'danger',
                'rota' => '/admin/institucional/dashboard',
            ];
        }

        // Permissão de visualizar logs
        if ($user->can('view audit-logs') || $user->hasRole('super-admin')) {
            $acoes[] = [
                'label' => 'Logs de Auditoria',
                'descricao' => 'Ver histórico do sistema',
                'icone' => 'fas fa-history',
                'cor' => 'secondary',
                'rota' => '/admin/logs',
            ];
        }

        return $acoes;
    }

    private function getAtividadesRecentes($user)
    {
        return AuditLog::where('user_id', $user->id)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'acao' => $log->action,
                    'modulo' => $log->module,
                    'descricao' => $log->description,
                    'data' => $log->created_at->format('d/m/Y H:i'),
                    'data_relativa' => $log->created_at->diffForHumans(),
                    'icone' => $this->getIconeAcao($log->action),
                ];
            });
    }

    private function getIconeAcao($action)
    {
        return match($action) {
            'created' => 'fas fa-plus-circle text-success',
            'updated' => 'fas fa-edit text-warning',
            'deleted' => 'fas fa-trash text-danger',
            default => 'fas fa-circle text-secondary',
        };
    }

    private function getWidgetsContextuais($user, $colaborador)
    {
        $widgets = [];

        // Widget: Aniversariantes do mês
        $aniversariantes = $this->getAniversariantesMes();
        if ($aniversariantes > 0) {
            $widgets[] = [
                'tipo' => 'aniversariantes',
                'titulo' => 'Aniversariantes do Mês',
                'valor' => $aniversariantes,
                'icone' => 'fas fa-birthday-cake',
                'cor' => 'purple',
                'link' => null,
            ];
        }

        // Widget: Equipe (se for gestor)
        if ($colaborador && $colaborador->is_gestor) {
            $equipe = Colaborador::where('gestor_imediato_id', $colaborador->id)
                ->where('status', 'Ativo')
                ->count();

            $widgets[] = [
                'tipo' => 'equipe',
                'titulo' => 'Minha Equipe',
                'valor' => $equipe,
                'icone' => 'fas fa-users',
                'cor' => 'blue',
                'link' => '/admin/pessoas-acessos/minha-equipe',
            ];
        }

        // Widget: Instituições (se tem permissão)
        if ($user->can('view institucional') || $user->hasRole('super-admin')) {
            $widgets[] = [
                'tipo' => 'instituicoes',
                'titulo' => 'Instituições Ativas',
                'valor' => Instituicao::where('status', 'Ativo')->count(),
                'icone' => 'fas fa-university',
                'cor' => 'green',
                'link' => '/admin/institucional/instituicoes',
            ];
        }

        // Widget: Colaboradores Ativos
        if ($user->can('view rh') || $user->hasRole('super-admin')) {
            $widgets[] = [
                'tipo' => 'colaboradores',
                'titulo' => 'Colaboradores Ativos',
                'valor' => Colaborador::where('status', 'Ativo')->count(),
                'icone' => 'fas fa-user-tie',
                'cor' => 'orange',
                'link' => '/admin/pessoas-acessos/colaboradores',
            ];
        }

        return $widgets;
    }

    private function getEstatisticasRapidas($user)
    {
        $stats = [];

        // Total de registros no sistema
        $stats['total_registros'] = GrupoEducacional::count() + 
                                   Mantenedora::count() + 
                                   Instituicao::count() + 
                                   Campus::count() + 
                                   Colaborador::count();

        // Atividade do usuário
        $stats['minhas_acoes_hoje'] = AuditLog::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $stats['minhas_acoes_semana'] = AuditLog::where('user_id', $user->id)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        return $stats;
    }

    private function getAniversariantesMes()
    {
        $mesAtual = Carbon::now()->month;
        
        return User::whereMonth('data_nascimento', $mesAtual)
            ->whereHas('colaborador', function($query) {
                $query->where('status', 'Ativo');
            })
            ->count();
    }

    private function getAniversariantesHoje()
    {
        $hoje = Carbon::now();
        
        return User::whereMonth('data_nascimento', $hoje->month)
            ->whereDay('data_nascimento', $hoje->day)
            ->whereHas('colaborador', function($query) {
                $query->where('status', 'Ativo');
            })
            ->count();
    }
}
