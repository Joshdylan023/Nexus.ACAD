<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Connector para ADP eXpert Brasil
 * 
 * Sistema específico para Brasil
 */
class ADPExpertConnector extends AbstractConnector
{
    protected ?string $sessionToken = null;

    /**
     * Testar conexão com ADP eXpert
     */
    public function testConnection(): array
    {
        try {
            $this->authenticate();

            if (!$this->sessionToken) {
                return [
                    'success' => false,
                    'message' => 'Falha na autenticação'
                ];
            }

            $baseUrl = $this->config['base_url'] ?? 'https://expert.adp.com.br';
            $response = $this->makeRequest('get', "{$baseUrl}/api/funcionarios/count");

            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'total_funcionarios' => $response['total'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão ADP eXpert: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Autenticar com ADP eXpert
     */
    protected function authenticate(): void
    {
        if ($this->sessionToken) {
            return;
        }

        try {
            $baseUrl = $this->config['base_url'] ?? 'https://expert.adp.com.br';
            $username = $this->config['username'] ?? null;
            $password = $this->config['password'] ?? null;
            $companyCode = $this->config['company_code'] ?? null;

            if (!$username || !$password || !$companyCode) {
                throw new \Exception('Credenciais incompletas');
            }

            $response = Http::post("{$baseUrl}/api/auth/login", [
                'usuario' => $username,
                'senha' => $password,
                'empresa' => $companyCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->sessionToken = $data['token'] ?? null;
                
                Log::info('ADP eXpert: Autenticação bem-sucedida');
            } else {
                throw new \Exception('Falha na autenticação: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Erro ao autenticar ADP eXpert: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar colaboradores do ADP eXpert
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $this->authenticate();

            $baseUrl = $this->config['base_url'] ?? 'https://expert.adp.com.br';
            $url = "{$baseUrl}/api/funcionarios";
            
            $params = [
                'limite' => 100,
                'pagina' => 1,
            ];

            if (!empty($filters['status'])) {
                $params['situacao'] = $filters['status'];
            }

            $response = $this->makeRequest('get', $url, $params);

            return $this->transformADPExpertEmployees($response['funcionarios'] ?? []);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores ADP eXpert: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar estrutura organizacional
     */
    public function fetchStructure(): array
    {
        try {
            $this->authenticate();

            $baseUrl = $this->config['base_url'] ?? 'https://expert.adp.com.br';
            $url = "{$baseUrl}/api/setores";

            $response = $this->makeRequest('get', $url);

            return $response['setores'] ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura ADP eXpert: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Transformar dados ADP eXpert para formato padrão
     */
    protected function transformADPExpertEmployees(array $employees): array
    {
        return array_map(function($emp) {
            return [
                'external_id' => $emp['codigo'] ?? null,
                'matricula_funcional' => $emp['matricula'] ?? null,
                'nome_completo' => $emp['nome'] ?? null,
                'cpf' => $emp['cpf'] ?? null,
                'email' => $emp['email'] ?? null,
                'telefone' => $emp['telefone'] ?? null,
                'cargo' => $emp['cargo'] ?? null,
                'departamento' => $emp['setor'] ?? null,
                'data_admissao' => $emp['dataAdmissao'] ?? null,
                'data_nascimento' => $emp['dataNascimento'] ?? null,
                'status' => $this->mapADPExpertStatus($emp['situacao'] ?? null),
                'is_gestor' => $emp['gestor'] === 'S',
            ];
        }, $employees);
    }

    /**
     * Mapear status ADP eXpert
     */
    protected function mapADPExpertStatus(?string $situacao): string
    {
        $statusMap = [
            'A' => 'ativo',
            'F' => 'afastado',
            'D' => 'desligado',
        ];
        
        return $statusMap[$situacao] ?? 'ativo';
    }

    /**
     * Headers específicos ADP eXpert
     */
    protected function getHeaders(): array
    {
        $this->authenticate();
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->sessionToken}",
        ];
    }
}
