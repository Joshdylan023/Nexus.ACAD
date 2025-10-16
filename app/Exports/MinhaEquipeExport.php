<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MinhaEquipeExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $dados;
    protected $titulo;

    public function __construct($dados, $titulo = 'Minha Equipe')
    {
        $this->dados = $dados;
        $this->titulo = $titulo;
    }

    public function collection()
    {
        return collect($this->dados)->map(function ($item) {
            return [
                'Nome' => $item['nome'],
                'Cargo' => $item['cargo'],
                'Matrícula' => $item['matricula'],
                'Email' => $item['email'],
                'Telefone' => $item['telefone'] ?? 'Não informado',
                'Setor' => $item['setor'] ?? 'Não informado',
                'Unidade' => $item['unidade'] ?? 'Não informado',
                'Data Admissão' => $item['data_admissao'] ?? 'Não informado',
                'Tipo' => $item['is_gestor'] ? 'Gestor' : 'Colaborador',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Cargo',
            'Matrícula',
            'Email',
            'Telefone',
            'Setor',
            'Unidade',
            'Data Admissão',
            'Tipo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Nome
            'B' => 35, // Cargo
            'C' => 15, // Matrícula
            'D' => 30, // Email
            'E' => 15, // Telefone
            'F' => 25, // Setor
            'G' => 25, // Unidade
            'H' => 15, // Data Admissão
            'I' => 15, // Tipo
        ];
    }
}
