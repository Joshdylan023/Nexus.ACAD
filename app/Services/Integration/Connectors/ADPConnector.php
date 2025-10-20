<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Connector para ADP Workforce Now
 * 
 * Documentação API: https://developers.adp.com/
 */
class ADPConnector extends AbstractConnector
{
    protected ?string $accessToken = null;

    /**
     * Testar conexão com ADP
     */
    public function testConnection(): array
    {
        try {
            $this->authenticate();

            if (!$this->accessToken) {
                return [
                    'success' => false,
                    'message' => 'Falha na autenticação'
                ];
            }

            // Testar endpoint de workers
            $baseUrl = $this->config['base_url'] ?? 'https://api.adp.com';
            $response = $this->makeRequest('get', "{$baseUrl}/hr/v2/workers?\$top=1");

            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'worker_count' => count($response['workers'] ?? [])
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão ADP: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Autenticar com ADP OAuth 2.0
     */
    protected function authenticate(): void
    {
        if ($this->accessToken) {
            return; // Já autenticado
        }

        try {
            $clientId = $this->config['client_id'] ?? null;
            $clientSecret = $this->config['client_secret'] ?? null;
            $authUrl = $this->config['auth_url'] ?? 'https://accounts.adp.com/auth/oauth/v2/token';

            if (!$clientId || !$clientSecret) {
                throw new \Exception('Client ID ou Client Secret não configurado');
            }

            $response = Http::asForm()->post($authUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? null;
                
                Log::info('ADP: Autenticação bem-sucedida');
            } else {
                throw new \Exception('Falha na autenticação: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Erro ao autenticar ADP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar colaboradores do ADP
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $this->authenticate();

            $baseUrl = $this->config['base_url'] ?? 'https://api.adp.com';
            $url = "{$baseUrl}/hr/v2/workers";
            
            $params = [
                '$top' => 100,
                '$skip' => 0,
            ];

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $params['$filter'] = "workerStatus/statusCode/codeValue eq '{$filters['status']}'";
            }

            $response = $this->makeRequest('get', $url, $params);

            // Transformar formato ADP → formato interno
            return $this->transformADPEmployees($response['workers'] ?? []);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores ADP: ' . $e->getMessage());
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

            $baseUrl = $this->config['base_url'] ?? 'https://api.adp.com';
            $url = "{$baseUrl}/hr/v2/organization-departments";

            $response = $this->makeRequest('get', $url);

            return $response['organizationDepartments'] ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura ADP: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Transformar dados ADP para formato padrão
     */
    protected function transformADPEmployees(array $workers): array
    {
        return array_map(function($worker) {
            $person = $worker['person'] ?? [];
            $legalName = $person['legalName'] ?? [];
            $businessCommunication = $person['businessCommunication'] ?? [];
            $workAssignment = $worker['workAssignments'][0] ?? [];
            
            return [
                'external_id' => $worker['associateOID'] ?? null,
                'matricula_funcional' => $workAssignment['workerID']['idValue'] ?? null,
                'nome_completo' => trim(($legalName['givenName'] ?? '') . ' ' . ($legalName['familyName1'] ?? '')),
                'cpf' => $person['governmentIDs'][0]['idValue'] ?? null,
                'email' => $businessCommunication['emails'][0]['emailUri'] ?? null,
                'telefone' => $businessCommunication['landlines'][0]['formattedNumber'] ?? null,
                'cargo' => $workAssignment['jobTitle'] ?? null,
                'departamento' => $workAssignment['organizationalUnits'][0]['nameCode']['longName'] ?? null,
                'data_admissao' => $workAssignment['hireDate'] ?? null,
                'data_nascimento' => $person['birthDate'] ?? null,
                'status' => $this->mapADPStatus($worker['workerStatus']['statusCode']['codeValue'] ?? null),
                'is_gestor' => isset($workAssignment['reportsTo']),
            ];
        }, $workers);
    }

    /**
     * Mapear status ADP
     */
    protected function mapADPStatus(?string $statusCode): string
    {
        $statusMap = [
            'Active' => 'ativo',
            'Leave' => 'afastado',
            'Terminated' => 'desligado',
        ];
        
        return $statusMap[$statusCode] ?? 'ativo';
    }

    /**
     * Headers específicos ADP
     */
    protected function getHeaders(): array
    {
        $this->authenticate();
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->accessToken}",
        ];
    }
}
