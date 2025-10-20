<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Http;

class SeniorConnector extends AbstractConnector
{
    protected string $baseUrl;
    protected string $accessToken;
    protected string $tenant;

    public function __construct($integration)
    {
        parent::__construct($integration);
        
        $this->baseUrl = $this->config['base_url'] ?? 'https://api.senior.com.br';
        $this->accessToken = $this->config['access_token'] ?? '';
        $this->tenant = $this->config['tenant'] ?? '';
    }

    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest('get', '/hcm/v1/colaboradores', [
                'query' => ['limit' => 1]
            ]);
            
            return [
                'success' => true,
                'message' => 'Conexão com Senior X estabelecida com sucesso',
                'tenant' => $this->tenant
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao conectar com Senior: ' . $e->getMessage()
            ];
        }
    }

    public function fetchEmployees(array $filters = []): array
    {
        $endpoint = '/hcm/v1/colaboradores';
        
        $params = [
            'limit' => 100,
            ...$filters
        ];

        $allEmployees = [];
        $page = 0;

        do {
            $params['offset'] = $page * 100;
            
            try {
                $response = $this->makeRequest('get', $endpoint, ['query' => $params]);
                $employees = $response['resultList'] ?? [];
                $allEmployees = array_merge($allEmployees, $employees);
                
                $hasMore = count($employees) === 100;
                $page++;
                
            } catch (\Exception $e) {
                \Log::error("Erro ao buscar colaboradores Senior: " . $e->getMessage());
                break;
            }
            
        } while ($hasMore && $page < 100);

        return $allEmployees;
    }

    public function fetchStructure(): array
    {
        return [
            'departamentos' => $this->fetchDepartments(),
            'cargos' => $this->fetchCargos(),
        ];
    }

    protected function fetchDepartments(): array
    {
        try {
            $response = $this->makeRequest('get', '/hcm/v1/departamentos');
            return $response['resultList'] ?? [];
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar departamentos Senior: " . $e->getMessage());
            return [];
        }
    }

    protected function fetchCargos(): array
    {
        try {
            $response = $this->makeRequest('get', '/hcm/v1/cargos');
            return $response['resultList'] ?? [];
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar cargos Senior: " . $e->getMessage());
            return [];
        }
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->accessToken}",
            'X-Tenant' => $this->tenant,
        ];
    }

    protected function applyFieldMapping(string $entityType, array $data): array
    {
        $seniorMapping = [
            'numCad' => 'matricula_funcional',
            'nomFun' => 'nome_completo',
            'cpfFun' => 'cpf',
            'datAdm' => 'data_admissao',
            'datNas' => 'data_nascimento',
            'codCar' => 'cargo',
            'codDep' => 'departamento',
            'sitAfa' => 'status',
        ];

        $mapped = [];
        foreach ($seniorMapping as $source => $target) {
            if (isset($data[$source])) {
                $mapped[$target] = $data[$source];
            }
        }

        return array_merge($mapped, parent::applyFieldMapping($entityType, $data));
    }
}
