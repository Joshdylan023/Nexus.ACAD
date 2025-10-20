<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;

/**
 * Connector para Senior X
 * 
 * Documentação API: https://dev.senior.com.br/
 */
class SeniorConnector extends AbstractConnector
{
    /**
     * Testar conexão com Senior
     */
    public function testConnection(): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? 'https://api.senior.com.br';
            $token = $this->config['access_token'] ?? null;
            $tenant = $this->config['tenant'] ?? null;

            if (!$token || !$tenant) {
                return [
                    'success' => false,
                    'message' => 'Token ou Tenant não configurado'
                ];
            }

            $response = $this->makeRequest('get', "{$baseUrl}/platform/hcm/api/health");

            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'tenant' => $tenant
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão Senior: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar colaboradores do Senior
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? 'https://api.senior.com.br';
            $url = "{$baseUrl}/platform/hcm/api/colaboradores";
            
            $params = [
                'limit' => 100,
                'offset' => 0,
            ];

            if (!empty($filters['status'])) {
                $params['situacao'] = $filters['status'];
            }

            $response = $this->makeRequest('get', $url, $params);

            return $this->transformSeniorEmployees($response['data'] ?? []);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores Senior: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar estrutura organizacional
     */
    public function fetchStructure(): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? 'https://api.senior.com.br';
            $url = "{$baseUrl}/platform/hcm/api/estrutura";

            $response = $this->makeRequest('get', $url);

            return $response['data'] ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura Senior: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Transformar dados Senior para formato padrão
     */
    protected function transformSeniorEmployees(array $employees): array
    {
        return array_map(function($emp) {
            return [
                'external_id' => $emp['id'] ?? null,
                'matricula_funcional' => $emp['matricula'] ?? null,
                'nome_completo' => $emp['nome'] ?? null,
                'cpf' => $emp['cpf'] ?? null,
                'email' => $emp['email'] ?? null,
                'telefone' => $emp['telefone'] ?? null,
                'cargo' => $emp['cargo'] ?? null,
                'departamento' => $emp['departamento'] ?? null,
                'data_admissao' => $emp['dataAdmissao'] ?? null,
                'data_nascimento' => $emp['dataNascimento'] ?? null,
                'status' => $this->mapSeniorStatus($emp['situacao'] ?? null),
                'is_gestor' => $emp['gestor'] ?? false,
            ];
        }, $employees);
    }

    /**
     * Mapear status Senior
     */
    protected function mapSeniorStatus(?string $situacao): string
    {
        $statusMap = [
            'A' => 'ativo',
            'ATIVO' => 'ativo',
            'F' => 'afastado',
            'AFASTADO' => 'afastado',
            'D' => 'desligado',
            'DEMITIDO' => 'desligado',
        ];
        
        return $statusMap[strtoupper($situacao ?? '')] ?? 'ativo';
    }

    /**
     * Headers específicos Senior
     */
    protected function getHeaders(): array
    {
        $token = $this->config['access_token'] ?? '';
        $tenant = $this->config['tenant'] ?? '';
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
            'tenant' => $tenant,
        ];
    }
}
