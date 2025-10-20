<?php

namespace App\Exports;

use App\Models\Curso;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CursosExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithColumnWidths,
    WithTitle,
    WithEvents
{
    protected $filters;
    protected $identidadeVisual;
    protected $instituicao;

    public function __construct($filters = [], $identidadeVisual = null, $instituicao = null)
    {
        $this->filters = $filters;
        $this->identidadeVisual = $identidadeVisual;
        $this->instituicao = $instituicao;
    }

    public function collection()
    {
        $query = Curso::with(['instituicao', 'areaConhecimento', 'coordenador']);

        // Aplica filtros
        if (!empty($this->filters['instituicao_id'])) {
            $query->where('instituicao_id', $this->filters['instituicao_id']);
        }

        if (!empty($this->filters['nivel'])) {
            $query->where('nivel', $this->filters['nivel']);
        }

        if (!empty($this->filters['modalidade'])) {
            $query->where('modalidade', $this->filters['modalidade']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo_ies', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código IES',
            'Nome do Curso',
            'Instituição',
            'Área de Conhecimento',
            'Nível',
            'Modalidade',
            'Duração (semestres)',
            'Prazo Máximo (semestres)',
            'Vagas Anuais',
            'Coordenador',
            'Status',
            'Criado em'
        ];
    }

    public function map($curso): array
    {
        return [
            $curso->id,
            $curso->codigo_ies,
            $curso->nome,
            $curso->instituicao->nome_fantasia ?? 'N/A',
            $curso->areaConhecimento->nome ?? 'N/A',
            $curso->nivel,
            ucfirst($curso->modalidade),
            $curso->duracao_padrao_semestres,
            $curso->prazo_maximo_semestres,
            $curso->vagas_anuais,
            $curso->coordenador->name ?? 'Sem coordenador',
            $curso->status,
            $curso->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ✅ COR PRIMÁRIA DA IDENTIDADE VISUAL
        $corPrimaria = $this->identidadeVisual 
            ? str_replace('#', '', $this->identidadeVisual->cor_primaria)
            : '667eea';

        return [
            1 => [
                'font' => [
                    'bold' => true, 
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $corPrimaria]
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 40,
            'D' => 30,
            'E' => 30,
            'F' => 15,
            'G' => 15,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 25,
            'L' => 15,
            'M' => 18,
        ];
    }

    public function title(): string
    {
        return $this->instituicao 
            ? 'Cursos - ' . substr($this->instituicao->nome_fantasia, 0, 20)
            : 'Cursos';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Adiciona informações da instituição no topo
                if ($this->instituicao) {
                    $sheet = $event->sheet->getDelegate();
                    
                    // Insere linha no topo
                    $sheet->insertNewRowBefore(1, 2);
                    
                    $sheet->setCellValue('A1', $this->instituicao->nome_fantasia);
                    $sheet->mergeCells('A1:M1');
                    
                    $sheet->setCellValue('A2', 'Relatório de Cursos - Gerado em: ' . now()->format('d/m/Y H:i'));
                    $sheet->mergeCells('A2:M2');
                    
                    // Estiliza o cabeçalho
                    $corPrimaria = $this->identidadeVisual 
                        ? str_replace('#', '', $this->identidadeVisual->cor_primaria)
                        : '667eea';
                    
                    $sheet->getStyle('A1:M2')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => 'center'],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $corPrimaria]
                        ]
                    ]);
                }
            }
        ];
    }
}
