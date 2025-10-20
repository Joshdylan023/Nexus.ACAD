<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardRHExport implements WithMultipleSheets
{
    protected $dados;
    
    public function __construct($dados)
    {
        $this->dados = $dados;
    }
    
    public function sheets(): array
    {
        return [
            new KPIsSheet($this->dados['kpis']),
            new MovimentacoesSheet($this->dados['movimentacoes']),
            new EvolucaoSheet($this->dados['evolucao'])
        ];
    }
}

// Sheet de KPIs
class KPIsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $kpis;
    
    public function __construct($kpis)
    {
        $this->kpis = $kpis;
    }
    
    public function title(): string
    {
        return 'KPIs Principais';
    }
    
    public function collection()
    {
        return collect([
            ['Total de Colaboradores', $this->kpis['totalColaboradores']],
            ['Colaboradores Ativos', $this->kpis['ativos']],
            ['Colaboradores Afastados', $this->kpis['afastados']],
            ['Colaboradores Desligados', $this->kpis['desligados']],
            ['Gestores', $this->kpis['gestores']],
            ['Admissões (período)', $this->kpis['admissoes']],
            ['Desligamentos (período)', $this->kpis['desligamentos']],
            ['Taxa de Turnover', $this->kpis['turnover'] . '%'],
            ['Tempo Médio de Casa', $this->kpis['tempoMedio'] . ' anos'],
            ['Aniversariantes do Mês', $this->kpis['aniversariantes']],
            ['Total de Setores', $this->kpis['totalSetores']],
        ]);
    }
    
    public function headings(): array
    {
        return ['Indicador', 'Valor'];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}

// Sheet de Movimentações
class MovimentacoesSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $movimentacoes;
    
    public function __construct($movimentacoes)
    {
        $this->movimentacoes = $movimentacoes;
    }
    
    public function title(): string
    {
        return 'Movimentações';
    }
    
    public function collection()
    {
        return collect($this->movimentacoes)->map(function($mov) {
            return [
                'data' => $mov['data'],
                'nome' => $mov['nome'],
                'tipo' => $mov['tipo'],
                'cargo' => $mov['cargo'],
                'setor' => $mov['setor']
            ];
        });
    }
    
    public function headings(): array
    {
        return ['Data', 'Nome', 'Tipo', 'Cargo', 'Setor'];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

// Sheet de Evolução
class EvolucaoSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $evolucao;
    
    public function __construct($evolucao)
    {
        $this->evolucao = $evolucao;
    }
    
    public function title(): string
    {
        return 'Evolução';
    }
    
    public function collection()
    {
        $dados = [];
        for ($i = 0; $i < count($this->evolucao['labels']); $i++) {
            $dados[] = [
                'mes' => $this->evolucao['labels'][$i],
                'admissoes' => $this->evolucao['admissoes'][$i],
                'desligamentos' => $this->evolucao['desligamentos'][$i],
                'saldo' => $this->evolucao['admissoes'][$i] - $this->evolucao['desligamentos'][$i]
            ];
        }
        return collect($dados);
    }
    
    public function headings(): array
    {
        return ['Mês', 'Admissões', 'Desligamentos', 'Saldo'];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
