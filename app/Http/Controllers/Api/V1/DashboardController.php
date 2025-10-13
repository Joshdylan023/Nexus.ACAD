<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Setor;
use App\Models\ImportLog;
use App\Models\SystemEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function institucional(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        return response()->json([
            'totais' => $this->getTotais(),
            'instituicoes_por_estado' => $this->getInstituicoesPorEstado(),
            'instituicoes_por_tipo' => $this->getInstituicoesPorTipo(),
            'atividades_recentes' => $this->getAtividadesRecentes(),
            'importacoes_recentes' => $this->getImportacoesRecentes(),
            'setores_ativos' => $this->getSetoresAtivos(),
            'evento_ativo' => $this->getEventoAtivo(),
            'evolucao_cadastros' => $this->getEvolucaoCadastros($startDate, $endDate),
            'mapa_brasil' => $this->getMapaBrasil()
        ]);
    }

    private function getTotais()
    {
        return [
            'grupos_educacionais' => GrupoEducacional::count(),
            'mantenedoras' => Mantenedora::count(),
            'instituicoes' => Instituicao::count(),
            'campi' => Campus::count(),
            'setores' => Setor::count()
        ];
    }

    private function getInstituicoesPorEstado()
    {
        return Instituicao::select('estado', DB::raw('count(*) as total'))
            ->whereNotNull('estado')
            ->where('estado', '!=', '')
            ->groupBy('estado')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function getInstituicoesPorTipo()
    {
        return Instituicao::select('tipo_organizacao_academica', DB::raw('count(*) as total'))
            ->whereNotNull('tipo_organizacao_academica')
            ->where('tipo_organizacao_academica', '!=', '')
            ->groupBy('tipo_organizacao_academica')
            ->get();
    }

    private function getAtividadesRecentes()
    {
        $atividades = [];

        // Últimas instituições criadas
        $instituicoes = Instituicao::latest()->limit(5)->get(['id', 'razao_social', 'created_at']);
        foreach ($instituicoes as $inst) {
            $atividades[] = [
                'tipo' => 'instituicao',
                'descricao' => "Nova instituição: {$inst->razao_social}",
                'data' => $inst->created_at,
                'icone' => 'building'
            ];
        }

        // Últimos campi criados
        $campi = Campus::with('instituicao')->latest()->limit(5)->get(['id', 'nome', 'instituicao_id', 'created_at']);
        foreach ($campi as $campus) {
            $atividades[] = [
                'tipo' => 'campus',
                'descricao' => "Novo campus: {$campus->nome}",
                'data' => $campus->created_at,
                'icone' => 'geo-alt'
            ];
        }

        // Ordena por data decrescente e pega as 10 mais recentes
        usort($atividades, fn($a, $b) => $b['data'] <=> $a['data']);
        return array_slice($atividades, 0, 10);
    }

    private function getImportacoesRecentes()
    {
        if (!Schema::hasTable('import_logs')) {
            return [];
        }

        return ImportLog::latest()
            ->limit(5)
            ->get(['id', 'import_type', 'file_name', 'total_rows', 'success_count', 'error_count', 'status', 'created_at']);
    }

    private function getSetoresAtivos()
    {
        return Setor::count();
    }

    private function getEventoAtivo()
    {
        // Verifica se a tabela existe
        if (!Schema::hasTable('system_events')) {
            return null;
        }
        
        // Busca evento ativo pela data atual
        $now = Carbon::now();
        
        return SystemEvent::where('status', 'active')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();
    }

    private function getEvolucaoCadastros($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Gera array com todos os meses no período
        $months = [];
        $current = $start->copy()->startOfMonth();
        
        while ($current <= $end) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        // Busca dados de instituições (PostgreSQL)
        $instituicoes = Instituicao::whereBetween('created_at', [$start, $end])
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as mes, COUNT(*) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Busca dados de campi (PostgreSQL)
        $campi = Campus::whereBetween('created_at', [$start, $end])
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as mes, COUNT(*) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Formata resposta
        $labels = [];
        $dataInstituicoes = [];
        $dataCampi = [];

        foreach ($months as $month) {
            $labels[] = Carbon::createFromFormat('Y-m', $month)->locale('pt_BR')->isoFormat('MMM/YY');
            $dataInstituicoes[] = $instituicoes[$month] ?? 0;
            $dataCampi[] = $campi[$month] ?? 0;
        }

        return [
            'labels' => $labels,
            'instituicoes' => $dataInstituicoes,
            'campi' => $dataCampi
        ];
    }

    private function getMapaBrasil()
    {
        $result = Instituicao::select('estado', DB::raw('count(*) as total'))
            ->whereNotNull('estado')
            ->where('estado', '!=', '')
            ->groupBy('estado')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->estado => $item->total];
            });

        return $result->isEmpty() ? (object)[] : $result;
    }
}
