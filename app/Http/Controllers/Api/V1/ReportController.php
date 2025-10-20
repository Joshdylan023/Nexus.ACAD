<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Colaborador;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Curso;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('creator');

        if ($request->user() && method_exists($request->user(), 'cannot')) {
            if ($request->user()->cannot('gerenciar-relatorios')) {
                $query->where(function ($q) use ($request) {
                    $q->where('created_by', $request->user()->id)
                      ->orWhere('is_public', true);
                });
            }
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:institucional,academico,rh,financeiro,colaboradores,setores,campi',
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
            'type' => 'in:institucional,academico,rh,financeiro,colaboradores,setores,campi',
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
        
        if (method_exists($report, 'incrementExecutionCount')) {
            $report->incrementExecutionCount();
        }

        $data = $this->generateReportData($report);

        return response()->json([
            'data' => $data,
            'report' => $report,
            'total' => count($data)
        ]);
    }

    // ✅ CORRIGIDO - Aceitar formato da URL
    public function export(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        
        if (method_exists($report, 'incrementExecutionCount')) {
            $report->incrementExecutionCount();
        }

        $data = $this->generateReportData($report);

        // ✅ Priorizar formato da URL, senão usar do relatório
        $format = $request->get('format', $report->format);

        switch ($format) {
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
            case 'colaboradores':
                return $this->generateColaboradoresReport($report);
            case 'setores':
                return $this->generateSetoresReport($report);
            case 'campi':
                return $this->generateCampiReport($report);
            default:
                return [];
        }
    }

    private function generateInstitucionalReport($report)
    {
        $query = Instituicao::query();

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

    // ✅ MÉTODO MELHORADO COM RELACIONAMENTOS
    private function generateColaboradoresReport($report)
    {
        $query = Colaborador::with([
            'usuario', 
            'setorVinculo.setor',
            'unidadeLotacao',
            'unidadeOrganizacional'
        ]);

        $filters = $report->filters;
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['is_gestor'])) {
            $query->where('is_gestor', $filters['is_gestor'] === 'true' || $filters['is_gestor'] === true);
        }

        if (!empty($filters['cargo'])) {
            $query->where('cargo', 'ILIKE', '%' . $filters['cargo'] . '%');
        }

        if (!empty($filters['data_admissao_inicio'])) {
            $query->where('data_admissao', '>=', $filters['data_admissao_inicio']);
        }

        if (!empty($filters['data_admissao_fim'])) {
            $query->where('data_admissao', '<=', $filters['data_admissao_fim']);
        }

        $columns = $report->columns;
        
        $result = $query->get()->map(function($colab) use ($columns) {
            $data = [];
            
            foreach ($columns as $col) {
                // ✅ PROCESSAR RELACIONAMENTOS COM DOT NOTATION
                if (str_contains($col, '.')) {
                    $data[$col] = data_get($colab, $col) ?? '';
                } 
                // Campos diretos do colaborador
                else {
                    $data[$col] = $colab->$col ?? '';
                }
            }
            
            return $data;
        });

        return $result->toArray();
    }

    private function generateSetoresReport($report)
    {
        $query = Setor::query();

        $filters = $report->filters;
        
        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        $columns = $report->columns;
        
        return $query->get($columns)->toArray();
    }

    private function generateCampiReport($report)
    {
        $query = Campus::query();

        $filters = $report->filters;
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['instituicao_id'])) {
            $query->where('instituicao_id', $filters['instituicao_id']);
        }

        $columns = $report->columns;
        
        return $query->get($columns)->toArray();
    }

    private function exportExcel($report, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnCount = count($report->columns);
        $lastColumn = chr(64 + min($columnCount, 26)); // Limitar a Z por enquanto

        // Título do relatório
        $sheet->setCellValue('A1', $report->name);
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Cabeçalhos (linha 3)
        $columns = $report->columns;
        $columnLabels = $this->getColumnLabelsArray($report->type);
        
        $col = 'A';
        foreach ($columns as $column) {
            $label = $columnLabels[$column] ?? ucfirst($column);
            $sheet->setCellValue($col . '3', $label);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('667EEA');
            $sheet->getStyle($col . '3')->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }

        // Dados
        $row = 4;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($columns as $column) {
                $value = $item[$column] ?? '';
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size colunas
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
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

    // ✅ CORRIGIDO - CSV com labels corretos
    private function exportCsv($report, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = $report->columns;
        $columnLabels = $this->getColumnLabelsArray($report->type);
        
        // Cabeçalhos com labels corretos
        $col = 'A';
        foreach ($columns as $column) {
            $label = $columnLabels[$column] ?? ucfirst($column);
            $sheet->setCellValue($col . '1', $label);
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

        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';'); // Usar ponto-e-vírgula para melhor compatibilidade
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

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
        return response()->json(['message' => 'Exportação PDF em desenvolvimento']);
    }

    // ✅ MÉTODO MELHORADO COM RELACIONAMENTOS
    public function getAvailableColumns($type)
    {
        $columns = [
            'institucional' => [
                'id' => 'ID',
                'razao_social' => 'Razão Social',
                'nome_fantasia' => 'Nome Fantasia',
                'sigla' => 'Sigla',
                'codigo_mec' => 'Código MEC',
                'tipo_organizacao_academica' => 'Tipo de Organização',
                'estado' => 'Estado',
                'cidade' => 'Cidade',
                'cnpj' => 'CNPJ',
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
            ],
            'colaboradores' => [
                'id' => 'ID',
                'matricula_funcional' => 'Matrícula',
                'usuario.name' => 'Nome Completo',
                'cargo' => 'Cargo',
                'email_funcional' => 'E-mail Funcional',
                'status' => 'Status',
                'is_gestor' => 'É Gestor?',
                'data_admissao' => 'Data de Admissão',
                'data_desligamento' => 'Data de Desligamento',
                
                // ✅ RELACIONAMENTOS
                'setorVinculo.setor.nome' => 'Setor',
                'setorVinculo.setor.tipo' => 'Tipo de Setor',
                'setorVinculo.setor.sigla' => 'Sigla do Setor',
                'unidadeLotacao.razao_social' => 'Unidade de Lotação',
                'unidadeLotacao.sigla' => 'Sigla da Lotação',
                'unidadeLotacao.tipo' => 'Tipo da Lotação',
                'unidadeOrganizacional.nome' => 'Unidade Organizacional',
                'unidadeOrganizacional.razao_social' => 'Razão Social da Unidade Org.',
                
                'created_at' => 'Data de Cadastro',
                'updated_at' => 'Última Atualização'
            ],
            'setores' => [
                'id' => 'ID',
                'nome' => 'Nome',
                'sigla' => 'Sigla',
                'tipo' => 'Tipo',
                'created_at' => 'Data de Cadastro'
            ],
            'campi' => [
                'id' => 'ID',
                'nome' => 'Nome',
                'endereco_completo' => 'Endereço',
                'status' => 'Status',
                'created_at' => 'Data de Cadastro'
            ]
        ];

        return response()->json($columns[$type] ?? []);
    }

    private function getColumnLabelsArray($type)
    {
        $response = $this->getAvailableColumns($type);
        return json_decode($response->getContent(), true);
    }
}
