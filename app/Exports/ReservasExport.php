<?php

namespace App\Exports;

use App\Models\ReservaEspaco;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ReservasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filtros;

    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = ReservaEspaco::with([
            'espacoFisico',
            'solicitante',
            'aprovador'
        ]);

        if (isset($this->filtros['data_inicio'])) {
            $query->where('data_inicio', '>=', $this->filtros['data_inicio']);
        }

        if (isset($this->filtros['data_fim'])) {
            $query->where('data_fim', '<=', $this->filtros['data_fim']);
        }

        if (isset($this->filtros['status'])) {
            $query->where('status', $this->filtros['status']);
        }

        if (isset($this->filtros['campus_id'])) {
            $query->whereHas('espacoFisico.andar.bloco.predio', function($q) {
                $q->where('campus_id', $this->filtros['campus_id']);
            });
        }

        return $query->orderBy('data_inicio', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Motivo',
            'Espaço Físico',
            'Tipo',
            'Data Início',
            'Data Fim',
            'Hora Início',
            'Hora Fim',
            'Finalidade',
            'Participantes',
            'Status',
            'Solicitante',
            'Data Solicitação',
            'Aprovador',
            'Data Aprovação',
        ];
    }

    public function map($reserva): array
    {
        return [
            $reserva->id,
            $reserva->motivo,
            $reserva->espacoFisico->nome ?? '-',
            $reserva->espacoFisico->tipo ?? '-',
            Carbon::parse($reserva->data_inicio)->format('d/m/Y'),
            Carbon::parse($reserva->data_fim)->format('d/m/Y'),
            $reserva->hora_inicio,
            $reserva->hora_fim,
            $reserva->finalidade ?? '-',
            $reserva->quantidade_pessoas ?? '-',
            $reserva->status,
            $reserva->solicitante->name ?? '-',
            Carbon::parse($reserva->created_at)->format('d/m/Y H:i'),
            $reserva->aprovador->name ?? '-',
            $reserva->data_aprovacao ? Carbon::parse($reserva->data_aprovacao)->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }

    public function title(): string
    {
        return 'Reservas';
    }
}
