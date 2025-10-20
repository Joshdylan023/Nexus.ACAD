<?php

namespace App\Services\Integration\Connectors;

use App\Models\HRIntegration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CSVConnector extends AbstractConnector
{
    /**
     * Testar conexão (verifica se o arquivo existe)
     */
    public function testConnection(): array
    {
        try {
            $filePath = $this->config['file_path'] ?? 'samples/colaboradores_rh.csv';
            
            if (!Storage::exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "Arquivo CSV não encontrado: {$filePath}",
                    'details' => [
                        'path_checked' => storage_path('app/' . $filePath),
                        'storage_exists' => Storage::exists($filePath)
                    ]
                ];
            }

            // Tentar ler o arquivo
            $content = Storage::get($filePath);
            $lines = explode("\n", $content);
            
            if (empty($lines)) {
                return [
                    'success' => false,
                    'message' => 'Arquivo CSV está vazio'
                ];
            }

            $headers = str_getcsv($lines[0]);
            
            return [
                'success' => true,
                'message' => 'Conexão com arquivo CSV estabelecida',
                'details' => [
                    'file_path' => $filePath,
                    'total_lines' => count($lines) - 1, // -1 para excluir header
                    'headers' => $headers
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão CSV: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao acessar arquivo CSV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar colaboradores do arquivo CSV
     */
    public function fetchEmployees(array $filters = []): array
    {
        $filePath = $this->config['file_path'] ?? 'samples/colaboradores_rh.csv';
        
        // Verificar se arquivo existe
        if (!Storage::exists($filePath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$filePath}");
        }

        try {
            $content = Storage::get($filePath);
            $lines = explode("\n", $content);
            
            if (empty($lines)) {
                Log::warning('Arquivo CSV vazio', ['file_path' => $filePath]);
                return [];
            }

            // Parse CSV
            $delimiter = $this->config['delimiter'] ?? ',';
            $headers = str_getcsv(array_shift($lines), $delimiter);
            $employees = [];
            
            foreach ($lines as $lineNumber => $line) {
                $line = trim($line);
                
                // Pular linhas vazias
                if (empty($line)) {
                    continue;
                }
                
                $data = str_getcsv($line, $delimiter);
                
                // Validar quantidade de colunas
                if (count($data) !== count($headers)) {
                    Log::warning('Linha com número incorreto de colunas', [
                        'line_number' => $lineNumber + 2, // +2 porque tiramos header e index começa em 0
                        'expected' => count($headers),
                        'found' => count($data),
                        'data' => $data
                    ]);
                    continue;
                }
                
                $employees[] = array_combine($headers, $data);
            }

            Log::info('Colaboradores carregados do CSV', [
                'file_path' => $filePath,
                'total_records' => count($employees)
            ]);

            return $employees;

        } catch (\Exception $e) {
            Log::error('Erro ao ler arquivo CSV', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Erro ao processar arquivo CSV: {$e->getMessage()}");
        }
    }

    /**
     * Buscar estrutura organizacional do CSV
     */
    public function fetchStructure(): array
    {
        // Implementar se necessário carregar estrutura de outro CSV
        $filePath = $this->config['structure_file_path'] ?? 'samples/estrutura_rh.csv';
        
        if (!Storage::exists($filePath)) {
            Log::info('Arquivo de estrutura não configurado', ['path' => $filePath]);
            return [];
        }

        try {
            $content = Storage::get($filePath);
            $lines = explode("\n", $content);
            
            if (empty($lines)) {
                return [];
            }

            $delimiter = $this->config['delimiter'] ?? ',';
            $headers = str_getcsv(array_shift($lines), $delimiter);
            $structure = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $data = str_getcsv($line, $delimiter);
                if (count($data) === count($headers)) {
                    $structure[] = array_combine($headers, $data);
                }
            }

            return $structure;

        } catch (\Exception $e) {
            Log::error('Erro ao ler estrutura do CSV: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validar configuração da integração CSV
     */
    public static function validateConfig(array $config): array
    {
        $errors = [];

        if (empty($config['file_path'])) {
            $errors['file_path'] = 'Caminho do arquivo CSV é obrigatório';
        } elseif (!Storage::exists($config['file_path'])) {
            $errors['file_path'] = 'Arquivo CSV não encontrado no caminho especificado';
        }

        return $errors;
    }

    /**
     * Obter campos disponíveis no CSV
     */
    public function getAvailableFields(): array
    {
        try {
            $filePath = $this->config['file_path'] ?? 'samples/colaboradores_rh.csv';
            
            if (!Storage::exists($filePath)) {
                return [];
            }

            $content = Storage::get($filePath);
            $lines = explode("\n", $content);
            
            if (empty($lines)) {
                return [];
            }

            $delimiter = $this->config['delimiter'] ?? ',';
            return str_getcsv($lines[0], $delimiter);

        } catch (\Exception $e) {
            Log::error('Erro ao obter campos do CSV: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ Obter exemplo de dados do CSV
     */
    public function getSampleData(int $rows = 5): array
    {
        try {
            $employees = $this->fetchEmployees();
            return array_slice($employees, 0, $rows);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de exemplo: ' . $e->getMessage());
            return [];
        }
    }
}