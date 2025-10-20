<?php

namespace App\Services\Integration;

use App\Models\HRIntegration;
use App\Services\Integration\Connectors\AbstractConnector;
use App\Services\Integration\Connectors\GenericAPIConnector;
use App\Services\Integration\Connectors\TOTVSConnector;
use App\Services\Integration\Connectors\ADPConnector;
use App\Services\Integration\Connectors\ADPExpertConnector;
use App\Services\Integration\Connectors\SAPConnector;
use App\Services\Integration\Connectors\OracleConnector;
use App\Services\Integration\Connectors\SeniorConnector;
use App\Services\Integration\Connectors\CSVConnector;

class ConnectorFactory
{
    /**
     * Criar conector baseado no provider
     */
    public static function make(HRIntegration $integration): AbstractConnector
    {
        return match($integration->provider) {
            'generic' => new GenericAPIConnector($integration),
            'totvs' => new TOTVSConnector($integration),
            'adp' => new ADPConnector($integration),
            'adp_expert' => new ADPExpertConnector($integration),
            'sap' => new SAPConnector($integration),
            'oracle' => new OracleConnector($integration),
            'senior' => new SeniorConnector($integration),
            'csv' => new CSVConnector($integration),
            default => throw new \Exception("Provider não suportado: {$integration->provider}")
        };
    }

    /**
     * Listar providers disponíveis
     */
    public static function availableProviders(): array
    {
        return [
            'generic' => [
                'name' => 'API Genérica',
                'description' => 'Integração com qualquer API REST',
                'icon' => 'bi-cloud',
                'supported' => true
            ],
            'totvs' => [
                'name' => 'TOTVS Protheus',
                'description' => 'ERP TOTVS Protheus',
                'icon' => 'bi-building',
                'supported' => true
            ],
            'adp' => [
                'name' => 'ADP Workforce Now',
                'description' => 'Plataforma HCM Global da ADP',
                'icon' => 'bi-people',
                'supported' => true
            ],
            'adp_expert' => [
                'name' => 'ADP eXpert',
                'description' => 'Sistema ADP eXpert Brasil',
                'icon' => 'bi-people-fill',
                'supported' => true,
                'country' => 'BR'
            ],
            'sap' => [
                'name' => 'SAP SuccessFactors',
                'description' => 'SAP SuccessFactors HCM',
                'icon' => 'bi-diagram-3',
                'supported' => true
            ],
            'oracle' => [
                'name' => 'Oracle HCM Cloud',
                'description' => 'Oracle Human Capital Management',
                'icon' => 'bi-database',
                'supported' => true
            ],
            'senior' => [
                'name' => 'Senior X',
                'description' => 'Sistema Senior X',
                'icon' => 'bi-briefcase',
                'supported' => true
            ],
            'csv' => [
                'name' => 'CSV/Excel',
                'description' => 'Importação manual via arquivo',
                'icon' => 'bi-file-earmark-spreadsheet',
                'supported' => true
            ]
        ];
    }

    /**
     * Obter campos de configuração por provider
     */
    public static function getConfigFields(string $provider): array
    {
        return match($provider) {
            'generic' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL Base da API',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://api.exemplo.com'
                ],
                [
                    'name' => 'auth_type',
                    'label' => 'Tipo de Autenticação',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        'bearer' => 'Bearer Token',
                        'basic' => 'Basic Auth',
                        'api_key' => 'API Key'
                    ]
                ],
                [
                    'name' => 'api_token',
                    'label' => 'Token de API',
                    'type' => 'password',
                    'required' => false
                ],
                [
                    'name' => 'employees_endpoint',
                    'label' => 'Endpoint de Colaboradores',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '/employees'
                ]
            ],
            
            'totvs' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL do TOTVS',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://totvs.empresa.com.br'
                ],
                [
                    'name' => 'tenant',
                    'label' => 'Tenant',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'api_token',
                    'label' => 'Token de API',
                    'type' => 'password',
                    'required' => true
                ]
            ],
            
            'adp' => [
                [
                    'name' => 'client_id',
                    'label' => 'Client ID',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'client_secret',
                    'label' => 'Client Secret',
                    'type' => 'password',
                    'required' => true
                ],
                [
                    'name' => 'api_url',
                    'label' => 'URL da API',
                    'type' => 'text',
                    'required' => true,
                    'default' => 'https://api.adp.com'
                ]
            ],
            
            'adp_expert' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL do ADP eXpert',
                    'type' => 'text',
                    'required' => true,
                    'default' => 'https://expert.adp.com.br',
                    'placeholder' => 'https://expert.adp.com.br'
                ],
                [
                    'name' => 'username',
                    'label' => 'Usuário',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'seu.usuario'
                ],
                [
                    'name' => 'password',
                    'label' => 'Senha',
                    'type' => 'password',
                    'required' => true
                ],
                [
                    'name' => 'company_code',
                    'label' => 'Código da Empresa',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '001'
                ]
            ],
            
            'sap' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL da API',
                    'type' => 'text',
                    'required' => true,
                    'default' => 'https://api.successfactors.com',
                    'placeholder' => 'https://api.successfactors.com'
                ],
                [
                    'name' => 'company_id',
                    'label' => 'Company ID',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'api_key',
                    'label' => 'API Key',
                    'type' => 'password',
                    'required' => true
                ]
            ],
            
            'oracle' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL do Oracle HCM',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://empresa.oraclecloud.com'
                ],
                [
                    'name' => 'username',
                    'label' => 'Usuário',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'password',
                    'label' => 'Senha',
                    'type' => 'password',
                    'required' => true
                ]
            ],
            
            'senior' => [
                [
                    'name' => 'base_url',
                    'label' => 'URL da API Senior',
                    'type' => 'text',
                    'required' => true,
                    'default' => 'https://api.senior.com.br',
                    'placeholder' => 'https://api.senior.com.br'
                ],
                [
                    'name' => 'tenant',
                    'label' => 'Tenant',
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'access_token',
                    'label' => 'Access Token',
                    'type' => 'password',
                    'required' => true
                ]
            ],
            
            'csv' => [
                [
                    'name' => 'delimiter',
                    'label' => 'Delimitador',
                    'type' => 'select',
                    'required' => true,
                    'default' => ',',
                    'options' => [
                        ',' => 'Vírgula (,)',
                        ';' => 'Ponto e vírgula (;)',
                        '\t' => 'Tabulação (TAB)'
                    ]
                ],
                [
                    'name' => 'encoding',
                    'label' => 'Codificação',
                    'type' => 'select',
                    'required' => true,
                    'default' => 'UTF-8',
                    'options' => [
                        'UTF-8' => 'UTF-8',
                        'ISO-8859-1' => 'ISO-8859-1 (Latin-1)',
                        'Windows-1252' => 'Windows-1252'
                    ]
                ]
            ],
            
            default => []
        };
    }
}
