<?php

namespace App\Exports;

use App\Models\CursosAtoRegulatorio;
use App\Models\Curso;
use App\Helpers\IdentidadeVisualHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CursosAtosRegulatoriosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $cursoId;
    protected $curso;
    protected $corPrimaria;

    public function __construct($cursoId)
    {
        $this->cursoId = $cursoId;
        $this->curso = Curso::with('instituicao')->find($cursoId);
        
        // ✅ Buscar identidade visual usando o helper
        $identidade = IdentidadeVisualHelper::buscarPorInstituicao($this->curso->instituicao);
        
        // Pegar cor primária da identidade visual
        $this->corPrimaria = $identidade->cor_primaria ?? '34495e';
        
        // Remover # se existir
        $this->corPrimaria = str_replace('#', '', $this->corPrimaria);
    }

    public function collection()
    {
        return CursosAtoRegulatorio::where('curso_id', $this->cursoId)
            ->with('curso')
            ->orderBy('data_publicacao_dou', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Instituição',
            'Curso',
            'Tipo do Ato',
            'Código MEC',
            'Código e-MEC',
            'Número da Portaria',
            'Data de Publicação DOU',
            'Data de Validade',
            'Status',
            'Link da Publicação',
        ];
    }

    public function map($ato): array
    {
        $status = 'N/A';
        if ($ato->data_validade_ato) {
            $diasRestantes = now()->diffInDays($ato->data_validade_ato, false);
            if ($diasRestantes < 0) {
                $status = 'VENCIDO';
            } elseif ($diasRestantes <= 90) {
                $status = "EXPIRA EM $diasRestantes DIAS";
            } else {
                $status = 'ATIVO';
            }
        }

        return [
            $ato->id,
            $ato->curso->instituicao->nome_fantasia ?? 'N/A',
            $ato->curso->nome ?? 'N/A',
            $ato->tipo_ato,
            $ato->codigo_mec,
            $ato->codigo_emec ?? 'N/A',
            $ato->numero_portaria,
            $ato->data_publicacao_dou ? date('d/m/Y', strtotime($ato->data_publicacao_dou)) : 'N/A',
            $ato->data_validade_ato ? date('d/m/Y', strtotime($ato->data_validade_ato)) : 'Sem validade',
            $status,
            $ato->link_publicacao ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->corPrimaria],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Atos Regulatórios';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Ajustar largura das colunas
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(8);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(18);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(18);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(40);
                
                // Altura da linha de cabeçalho
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(25);
                
                // Adicionar bordas
                $highestRow = $event->sheet->getDelegate()->getHighestRow();
                $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
                
                $event->sheet->getDelegate()->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
