<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('creator');

        if ($request->user()->cannot('gerenciar-relatorios')) {
            $query->where(function ($q) use ($request) {
                $q->where('created_by', $request->user()->id)
                  ->orWhere('is_public', true);
            });
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:institucional,academico,rh,financeiro',
            'filters' => 'required|array',
            'columns' => 'required|array',
            'format' => 'required|in:excel,pdf,csv',
            'is_public' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule_frequency' => 'nullable|in:daily,weekly,monthly'
        ]);

        $validated['created_by'] = $request->user()->id;

        $report = Report::create($validated);

        return response()->json($report, 201);
    }

    public function show($id)
    {
        $report = Report::with('creator')->findOrFail($id);
        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:institucional,academico,rh,financeiro',
            'filters' => 'array',
            'columns' => 'array',
            'format' => 'in:excel,pdf,csv',
            'is_public' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule_frequency' => 'nullable|in:daily,weekly,monthly'
        ]);

        $report->update($validated);

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Relatório excluído com sucesso']);
    }

    public function execute($id)
    {
        $report = Report::findOrFail($id);
        $report->incrementExecutionCount();

        $data = $this->generateReportData($report);

        return response()->json([
            'data' => $data,
            'report' => $report
        ]);
    }

    public function export($id)
    {
        $report = Report::findOrFail($id);
        $report->incrementExecutionCount();

        $data = $this->generateReportData($report);

        switch ($report->format) {
            case 'excel':
                return $this->exportExcel($report, $data);
            case 'csv':
                return $this->exportCsv($report, $data);
            case 'pdf':
                return $this->exportPdf($report, $data);
            default:
                return $this->exportExcel($report, $data);
        }
    }

    private function generateReportData($report)
    {
        switch ($report->type) {
            case 'institucional':
                return $this->generateInstitucionalReport($report);
            case 'academico':
                return $this->generateAcademicoReport($report);
            case 'rh':
                return $this->generateRhReport($report);
            default:
                return [];
        }
    }

    private function generateInstitucionalReport($report)
    {
        $query = Instituicao::query();

        // Aplicar filtros
        $filters = $report->filters;
        
        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_organizacao'])) {
            $query->where('tipo_organizacao_academica', $filters['tipo_organizacao']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->where('created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->where('created_at', '<=', $filters['data_fim']);
        }

        // Selecionar colunas
        $columns = $report->columns;
        
        return $query->get($columns)->toArray();
    }

    private function generateAcademicoReport($report)
    {
        $query = Curso::query();

        $filters = $report->filters;
        
        if (!empty($filters['grau_academico'])) {
            $query->where('grau_academico', $filters['grau_academico']);
        }

        if (!empty($filters['modalidade'])) {
            $query->where('modalidade', $filters['modalidade']);
        }

        $columns = $report->columns;
        
        return $query->get($columns)->toArray();
    }

    private function generateRhReport($report)
    {
        $query = User::query();

        $filters = $report->filters;
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $columns = $report->columns;
        
        return $query->get($columns)->toArray();
    }

    private function exportExcel($report, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeçalhos
        $columns = $report->columns;
        $col = 'A';
        foreach ($columns as $column) {
            $sheet->setCellValue($col . '1', $column);
            $col++;
        }

        // Dados
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($columns as $column) {
                $sheet->setCellValue($col . $row, $item[$column] ?? '');
                $col++;
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'relatorio_' . $report->id . '_' . date('Y-m-d_His') . '.xlsx';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $writer->save($filepath);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    private function exportCsv($report, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = $report->columns;
        $col = 'A';
        foreach ($columns as $column) {
            $sheet->setCellValue($col . '1', $column);
            $col++;
        }

        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($columns as $column) {
                $sheet->setCellValue($col . $row, $item[$column] ?? '');
                $col++;
            }
            $row++;
        }

        $writer = new Csv($spreadsheet);
        $filename = 'relatorio_' . $report->id . '_' . date('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/public/reports/' . $filename);

        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $writer->save($filepath);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    private function exportPdf($report, $data)
    {
        // Implementar exportação PDF usando DomPDF ou similar
        return response()->json(['message' => 'Exportação PDF em desenvolvimento']);
    }

    public function getAvailableColumns($type)
    {
        $columns = [
            'institucional' => [
                'id' => 'ID',
                'razao_social' => 'Razão Social',
                'sigla' => 'Sigla',
                'codigo_mec' => 'Código MEC',
                'tipo_organizacao_academica' => 'Tipo de Organização',
                'estado' => 'Estado',
                'cidade' => 'Cidade',
                'created_at' => 'Data de Cadastro'
            ],
            'academico' => [
                'id' => 'ID',
                'nome' => 'Nome',
                'codigo' => 'Código',
                'grau_academico' => 'Grau Acadêmico',
                'modalidade' => 'Modalidade',
                'carga_horaria' => 'Carga Horária',
                'created_at' => 'Data de Cadastro'
            ],
            'rh' => [
                'id' => 'ID',
                'name' => 'Nome',
                'email' => 'E-mail',
                'created_at' => 'Data de Cadastro'
            ]
        ];

        return response()->json($columns[$type] ?? []);
    }
}
