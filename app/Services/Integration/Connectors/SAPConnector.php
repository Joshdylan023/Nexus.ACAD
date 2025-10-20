<?php

namespace App\Services\Integration\Connectors;

use Illuminate\Support\Facades\Log;

/**
 * Connector para SAP SuccessFactors
 * 
 * Documentação API: https://api.sap.com/api/PLTUserManagement/overview
 */
class SAPConnector extends AbstractConnector
{
    /**
     * Testar conexão com SAP
     */
    public function testConnection(): array
    {
        try {
            $baseUrl = $this->config['base_url'] ?? null;
            $apiKey = $this->config['api_key'] ?? null;

            if (!$baseUrl || !$apiKey) {
                return [
                    'success' => false,
                    'message' => 'URL base ou API Key não configurado'
                ];
            }

            // Testar endpoint OData
            $response = $this->makeRequest('get', "{$baseUrl}/odata/v2/User?\$top=1");

            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'user_count' => $response['d']['__count'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão SAP: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar colaboradores do SAP
     */
    public function fetchEmployees(array $filters = []): array
    {
        try {
            $baseUrl = $this->config['base_url'];
            
            // OData query SAP SuccessFactors
            $url = "{$baseUrl}/odata/v2/EmpEmployment";
            
            $params = [
                '$format' => 'json',
                '$expand' => 'personNav,jobInfoNav',
                '$filter' => "endDate eq null",
                '$top' => 1000
            ];

            $response = $this->makeRequest('get', $url, $params);

            // Transformar formato SAP → formato interno
            return $this->transformSAPEmployees($response['d']['results'] ?? []);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores SAP: ' . $e->getMessage());
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
            $url = "{$baseUrl}/odata/v2/FODepartment";

            $params = [
                '$format' => 'json',
                '$top' => 1000
            ];

            $response = $this->makeRequest('get', $url, $params);

            return $response['d']['results'] ?? [];

        } catch (\Exception $e) {
            Log::error('Erro ao buscar estrutura SAP: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Transformar dados SAP para formato padrão
     */
    protected function transformSAPEmployees(array $employees): array
    {
        return array_map(function($emp) {
            $person = $emp['personNav'] ?? [];
            $jobInfo = $emp['jobInfoNav'] ?? [];
            
            return [
                'external_id' => $emp['personIdExternal'] ?? null,
                'matricula_funcional' => $emp['userId'] ?? null,
                'nome_completo' => trim(($person['firstName'] ?? '') . ' ' . ($person['lastName'] ?? '')),
                'cpf' => $person['customString1'] ?? null, // Campo customizado
                'email' => $person['email'] ?? null,
                'telefone' => $person['phoneNav']['phoneNumber'] ?? null,
                'cargo' => $jobInfo['jobTitle'] ?? null,
                'departamento' => $jobInfo['department'] ?? null,
                'data_admissao' => $emp['startDate'] ?? null,
                'data_nascimento' => $person['dateOfBirth'] ?? null,
                'status' => $this->mapSAPStatus($emp['endDate'] ?? null),
                'is_gestor' => $jobInfo['isManager'] === 'true',
            ];
        }, $employees);
    }

    /**
     * Mapear status SAP
     */
    protected function mapSAPStatus(?string $endDate): string
    {
        return $endDate ? 'desligado' : 'ativo';
    }

    /**
     * Headers específicos SAP
     */
    protected function getHeaders(): array
    {
        $apiKey = $this->config['api_key'] ?? null;
        $companyId = $this->config['company_id'] ?? '';
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'APIKey' => $apiKey,
            'CompanyID' => $companyId,
        ];
    }
}
