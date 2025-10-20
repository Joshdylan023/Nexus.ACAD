<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;

/**
 * Connector para TOTVS Protheus/RM
 * 
 * Documentação API: https://api.totvs.com.br/
 */
class TOTVSConnector extends AbstractConnector
{
    /**
     * Testar conexão com TOTVS
     */
    public function testConnection(): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? null;
            $token = $this->config['token'] ?? null;

            if (!$baseUrl || !$token) {
                return [
                    'success' => false,
                    'message' => 'URL base ou token não configurado'
                ];
            }

            // Testar endpoint de health/status
            $response = $this->makeRequest('get', "{$baseUrl}/api/status");

            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'details' => $response
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão TOTVS: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar colaboradores do TOTVS
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $baseUrl = $this->config['base_url'];
            $company = $this->config['company_code'] ?? '01';
            $branch = $this->config['branch_code'] ?? '01';

            // Endpoint TOTVS para funcionários
            $url = "{$baseUrl}/api/rh/v1/employees";
            
            $params = [
                'company' => $company,
                'branch' => $branch,
                'pageSize' => 100,
                'page' => 1
            ];

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['department'])) {
                $params['department'] = $filters['department'];
            }

            $response = $this->makeRequest('get', $url, $params);

            // Transformar formato TOTVS → formato interno
            return $this->transformTOTVSEmployees($response['items'] ?? []);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores TOTVS: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar estrutura organizacional
     */
    public function fetchStructure(): array
    {
        try {
            $baseUrl = $this->config['base_url'];
            $url = "{$baseUrl}/api/rh/v1/departments";

            $response = $this->makeRequest('get', $url);

            return $response['items'] ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura TOTVS: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Transformar dados TOTVS para formato padrão
     */
    protected function transformTOTVSEmployees(array $employees): array
    {
        return array_map(function($emp) {
            return [
                'external_id' => $emp['id'] ?? null,
                'matricula_funcional' => $emp['registration'] ?? $emp['code'] ?? null,
                'nome_completo' => $emp['name'] ?? null,
                'cpf' => $this->formatCPF($emp['cpf'] ?? null),
                'email' => $emp['email'] ?? null,
                'telefone' => $emp['phone'] ?? null,
                'cargo' => $emp['position'] ?? $emp['role'] ?? null,
                'departamento' => $emp['department'] ?? null,
                'data_admissao' => $emp['admissionDate'] ?? null,
                'data_nascimento' => $emp['birthDate'] ?? null,
                'status' => $emp['status'] ?? 'ativo',
                'is_gestor' => $emp['isManager'] ?? false,
            ];
        }, $employees);
    }

    /**
     * Formatar CPF
     */
    protected function formatCPF(?string $cpf): ?string
    {
        if (!$cpf) return null;
        
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . 
                   substr($cpf, 3, 3) . '.' . 
                   substr($cpf, 6, 3) . '-' . 
                   substr($cpf, 9, 2);
        }
        
        return $cpf;
    }

    /**
     * Headers específicos TOTVS
     */
    protected function getHeaders(): array
    {
        $token = $this->config['token'] ?? null;
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
            'tenant-id' => $this->config['tenant_id'] ?? '',
        ];
    }
}
