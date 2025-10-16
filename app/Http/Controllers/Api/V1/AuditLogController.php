<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\AuditLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    /**
     * Listar logs com filtros e paginação
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->buildFilteredQuery($request);

        // Ordenação
        $query->orderBy('created_at', 'desc');

        // Paginação
        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Detalhes de um log específico
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load(['user:id,name,email', 'auditable']);
        
        return response()->json([
            'log' => $auditLog,
            'changes' => $auditLog->getChanges(),
        ]);
    }

    /**
     * Estatísticas dos logs
     */
    public function statistics(Request $request): JsonResponse
    {
        $module = $request->get('module');
        
        $query = AuditLog::query();
        
        if ($module) {
            $query->module($module);
        }

        $stats = [
            'total' => $query->count(),
            'by_action' => $query->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->get()
                ->pluck('count', 'action'),
            'by_module' => AuditLog::selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->get()
                ->pluck('count', 'module'),
            'recent_users' => AuditLog::with('user:id,name')
                ->select('user_id')
                ->groupBy('user_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->get()
                ->pluck('user.name', 'user_id'),
        ];

        return response()->json($stats);
    }

    /**
     * Exportar logs para Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only(['module', 'action', 'date_from', 'date_to', 'search']);
            
            return Excel::download(
                new AuditLogsExport($filters),
                'logs-auditoria-' . now()->format('Y-m-d-His') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao exportar para Excel',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar logs para PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildFilteredQuery($request);

            // Limitar para performance (máximo 1000 registros)
            // Usar chunk para evitar problemas de memória com grandes datasets.
            $logs = $query->latest()->limit(1000)->cursor();

            $pdf = Pdf::loadView('pdf.audit-logs', [
                'logs' => $logs,
                'filters' => $request->only(['module', 'action', 'date_from', 'date_to', 'search']),
            ])->setPaper('a4', 'landscape')->setOption('enable-local-file-access', true);

            return $pdf->download('logs-auditoria-' . now()->format('Y-m-d-His') . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao exportar para PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Constrói a query de logs com base nos filtros da request.
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $query = AuditLog::with(['user:id,name,email', 'auditable']);

        // Filtro por módulo
        if ($request->filled('module')) {
            $query->module($request->module);
        }

        // Filtro por ação
        if ($request->filled('action')) {
            $query->action($request->action);
        }

        // Filtro por tipo de entidade
        if ($request->filled('auditable_type')) {
            $query->auditableType($request->auditable_type);
        }

        // Filtro por usuário
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filtro por data
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Busca textual
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query;
    }
}
