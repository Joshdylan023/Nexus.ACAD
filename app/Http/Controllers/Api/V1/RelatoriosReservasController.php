<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReservaEspaco;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservasExport;
use Carbon\Carbon;

class RelatoriosReservasController extends Controller
{
    /**
     * Exportar Relatório PDF
     */
    public function exportarPDF(Request $request)
    {
        $reservas = $this->getReservasFiltradas($request);
        
        $pdf = Pdf::loadView('relatorios.reservas-pdf', [
            'reservas' => $reservas,
            'filtros' => $request->all(),
            'data_geracao' => now()->format('d/m/Y H:i'),
        ]);
        
        return $pdf->download('relatorio-reservas-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar Relatório Excel
     */
    public function exportarExcel(Request $request)
    {
        return Excel::download(
            new ReservasExport($request->all()), 
            'relatorio-reservas-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Relatório de Ocupação
     */
    public function relatorioOcupacao(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth());
        
        $ocupacao = ReservaEspaco::with('espacoFisico')
            ->whereBetween('data_inicio', [$dataInicio, $dataFim])
            ->whereIn('status', ['Aprovada', 'Concluída'])
            ->get()
            ->groupBy('espaco_fisico_id')
            ->map(function($reservas) {
                $espaco = $reservas->first()->espacoFisico;
                return [
                    'espaco' => $espaco->nome,
                    'codigo' => $espaco->codigo,
                    'tipo' => $espaco->tipo,
                    'total_reservas' => $reservas->count(),
                    'horas_utilizadas' => $this->calcularHorasUtilizadas($reservas),
                ];
            })
            ->values();

        return response()->json($ocupacao);
    }

    /**
     * Relatório por Solicitante
     */
    public function relatorioPorSolicitante(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth());
        
        $dados = ReservaEspaco::with('solicitante')
            ->whereBetween('data_inicio', [$dataInicio, $dataFim])
            ->get()
            ->groupBy('solicitante_id')
            ->map(function($reservas) {
                return [
                    'solicitante' => $reservas->first()->solicitante->name,
                    'total_reservas' => $reservas->count(),
                    'pendentes' => $reservas->where('status', 'Pendente')->count(),
                    'aprovadas' => $reservas->where('status', 'Aprovada')->count(),
                    'rejeitadas' => $reservas->where('status', 'Rejeitada')->count(),
                ];
            })
            ->values();

        return response()->json($dados);
    }

    private function getReservasFiltradas(Request $request)
    {
        $query = ReservaEspaco::with(['espacoFisico', 'solicitante', 'aprovador']);
        
        if ($request->filled('data_inicio')) {
            $query->where('data_inicio', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->where('data_fim', '<=', $request->data_fim);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('campus_id')) {
            $query->whereHas('espacoFisico.andar.bloco.predio', function($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }
        
        return $query->orderBy('data_inicio', 'desc')->get();
    }

    private function calcularHorasUtilizadas($reservas)
    {
        return $reservas->sum(function($reserva) {
            $inicio = Carbon::parse($reserva->hora_inicio);
            $fim = Carbon::parse($reserva->hora_fim);
            return $fim->diffInHours($inicio);
        });
    }
}
