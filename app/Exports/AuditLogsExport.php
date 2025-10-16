<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = AuditLog::with(['user:id,name,email']);

        // Filtro por módulo
        if (!empty($this->filters['module'])) {
            $query->where('module', $this->filters['module']);
        }

        // Filtro por ação
        if (!empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }

        // Filtro por data
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        // Busca textual
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Data/Hora',
            'Usuário',
            'Ação',
            'Módulo',
            'Descrição',
            'Endereço IP',
            'Tipo de Entidade',
            'ID da Entidade',
        ];
    }

    public function map($log): array
    {
        $actions = [
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Exclusão',
            'restored' => 'Restauração',
        ];

        $modules = [
            'institucional' => 'Institucional',
            'pessoas_acessos' => 'Pessoas & Acessos',
            'academico' => 'Acadêmico',
            'financeiro' => 'Financeiro',
            'estagios' => 'Estágios',
            'sistema' => 'Sistema',
        ];

        return [
            $log->id,
            $log->created_at->format('d/m/Y H:i:s'),
            $log->user?->name ?? 'Sistema',
            $actions[$log->action] ?? $log->action,
            $modules[$log->module] ?? $log->module,
            $log->description,
            $log->ip_address ?? 'N/A',
            $log->auditable_type,
            $log->auditable_id,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para o cabeçalho
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Auto-size para as colunas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}