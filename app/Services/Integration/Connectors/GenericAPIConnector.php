<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;

/**
 * Connector genérico para APIs REST customizadas
 */
class GenericAPIConnector extends AbstractConnector
{
    /**
     * Testar conexão genérica
     */
    public function testConnection(): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? null;

            if (!$baseUrl) {
                return [
                    'success' => false,
                    'message' => 'URL base não configurada'
                ];
            }

            // Tentar ping no endpoint
            $response = $this->makeRequest('get', $baseUrl);

            return [
                'success' => true,
                'message' => 'Conexão estabelecida',
                'response' => $response
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar colaboradores da API genérica
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $baseUrl = $this->config['base_url'];
            $endpoint = $this->config['employees_endpoint'] ?? '/employees';
            $url = $baseUrl . $endpoint;

            $response = $this->makeRequest('get', $url, $filters);

            // Assumir que retorna array direto ou dentro de 'data'
            $employees = $response['data'] ?? $response;

            return is_array($employees) ? $employees : [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores da API genérica: ' . $e->getMessage());
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
            $endpoint = $this->config['structure_endpoint'] ?? '/departments';
            $url = $baseUrl . $endpoint;

            $response = $this->makeRequest('get', $url);

            return $response['data'] ?? $response ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura da API genérica: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Headers para API genérica
     */
    protected function getHeaders(): array
    {
        $authType = $this->config['auth_type'] ?? 'bearer';
        $token = $this->config['api_token'] ?? null;

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($token) {
            if ($authType === 'bearer') {
                $headers['Authorization'] = "Bearer {$token}";
            } elseif ($authType === 'api_key') {
                $headers['X-API-Key'] = $token;
            }
        }

        return $headers;
    }
}
