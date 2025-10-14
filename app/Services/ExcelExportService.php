<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExcelExportService
{
    protected $spreadsheet;
    protected $identidade;
    protected $metadata;
    
    public function __construct($identidade = null, $metadata = [])
    {
        $this->spreadsheet = new Spreadsheet();
        $this->identidade = $identidade;
        $this->metadata = $metadata;
    }
    
    /**
     * Criar aba de dados
     */
    public function createDataSheet($title, $headers, $data)
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle($title);
        
        // Adicionar logo e metadados
        $this->addLogoAndMetadata($sheet);
        
        // Adicionar cabeçalhos na linha 7
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '7', $header);
            $col++;
        }
        
        // Estilizar cabeçalhos
        $this->styleHeaders($sheet, count($headers));
        
        // Adicionar dados
        $row = 8;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($item as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Aplicar bordas
        $this->applyBorders($sheet, count($headers), $row - 1);
        
        // Auto-size nas colunas
        foreach (range('A', chr(64 + count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Congelar primeira linha de dados
        $sheet->freezePane('A8');
        
        return $sheet;
    }
    
    /**
     * Criar aba de estatísticas
     */
    public function createStatsSheet($title, $stats)
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle($title);
        
        // Cabeçalhos
        $sheet->setCellValue('A1', 'Métrica');
        $sheet->setCellValue('B1', 'Valor');
        
        // Estilizar cabeçalhos
        $corPrimaria = $this->getCorPrimaria();
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $corPrimaria],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Dados
        $row = 2;
        foreach ($stats as $stat) {
            $sheet->setCellValue('A' . $row, $stat[0]);
            $sheet->setCellValue('B' . $row, $stat[1]);
            $row++;
        }
        
        // Auto-size
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        
        return $sheet;
    }
    
    /**
     * Adicionar logo e metadados
     */
    protected function addLogoAndMetadata($sheet)
    {
        // Logo
        if ($this->identidade && $this->identidade->logo_principal) {
            $logoPath = storage_path('app/public/' . $this->identidade->logo_principal);
            
            if (file_exists($logoPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo da Instituição');
                $drawing->setPath($logoPath);
                $drawing->setHeight(100);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
            }
        }
        
        $corPrimaria = $this->getCorPrimaria();
        
        // Título
        $sheet->setCellValue('D1', $this->metadata['titulo'] ?? 'Relatório');
        $sheet->getStyle('D1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => $corPrimaria]],
        ]);
        
        // Metadados
        $sheet->setCellValue('D3', 'Data de Geração:');
        $sheet->setCellValue('E3', now()->format('d/m/Y H:i:s'));
        $sheet->getStyle('D3')->getFont()->setBold(true);
        
        $sheet->setCellValue('D4', 'Gerado por:');
        $sheet->setCellValue('E4', $this->metadata['usuario'] ?? auth()->user()->name);
        $sheet->getStyle('D4')->getFont()->setBold(true);
        
        $sheet->setCellValue('D5', 'Total de Registros:');
        $sheet->setCellValue('E5', $this->metadata['total'] ?? 0);
        $sheet->getStyle('D5')->getFont()->setBold(true);
    }
    
    /**
     * Estilizar cabeçalhos
     */
    protected function styleHeaders($sheet, $colCount)
    {
        $corPrimaria = $this->getCorPrimaria();
        $lastCol = chr(64 + $colCount);
        
        $sheet->getStyle('A7:' . $lastCol . '7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $corPrimaria],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
    
    /**
     * Aplicar bordas
     */
    protected function applyBorders($sheet, $colCount, $lastRow)
    {
        $lastCol = chr(64 + $colCount);
        
        $sheet->getStyle('A7:' . $lastCol . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
    }
    
    /**
     * Obter cor primária
     */
    protected function getCorPrimaria()
    {
        if ($this->identidade && $this->identidade->cor_primaria) {
            return ltrim($this->identidade->cor_primaria, '#');
        }
        return '667EEA';
    }
    
    /**
     * Remover a primeira aba vazia
     */
    public function removeDefaultSheet()
    {
        $this->spreadsheet->removeSheetByIndex(0);
    }
    
    /**
     * Download do arquivo
     */
    public function download($filename)
    {
        $this->removeDefaultSheet();
        
        $writer = new Xlsx($this->spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
