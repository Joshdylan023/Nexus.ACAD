<?php

namespace App\Services\Integration\Connectors;

use App\Models\HRIntegration;
use App\Models\HRSyncLog;
use App\Models\HRSyncError;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

abstract class AbstractConnector
{
    protected HRIntegration $integration;
    protected HRSyncLog $syncLog;
    protected array $config;
    protected array $stats = [
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'skipped' => 0
    ];

    public function __construct(HRIntegration $integration)
    {
        $this->integration = $integration;
        $this->config = $integration->config ?? [];
    }

    /**
     * Testar conexão com o sistema externo
     */
    abstract public function testConnection(): array;

    /**
     * Buscar colaboradores do sistema externo
     */
    abstract public function fetchEmployees(array $filters = []): array;

    /**
     * Buscar estrutura organizacional
     */
    abstract public function fetchStructure(): array;

    /**
     * Sincronizar dados
     */
    public function sync(string $type = 'colaboradores', array $options = []): HRSyncLog
    {
        // Criar log de sincronização
        $this->syncLog = HRSyncLog::create([
            'hr_integration_id' => $this->integration->id,
            'type' => $type,
            'status' => 'pending',
            'triggered_by' => auth()->id(),
            'trigger_type' => $options['trigger_type'] ?? 'manual'
        ]);

        try {
            $this->syncLog->start();

            // Executar sincronização baseado no tipo
            match($type) {
                'colaboradores' => $this->syncEmployees($options),
                'estrutura' => $this->syncStructure($options),
                'completo' => $this->syncComplete($options),
                default => throw new \Exception("Tipo de sincronização inválido: {$type}")
            };

            // Completar log
            $this->syncLog->complete([
                'records_created' => $this->stats['created'],
                'records_updated' => $this->stats['updated'],
                'records_failed' => $this->stats['failed'],
                'records_skipped' => $this->stats['skipped'],
            ]);

            // Atualizar última sincronização
            $this->integration->update([
                'last_sync_at' => now()
            ]);
            $this->integration->calculateNextSync();
            $this->integration->save();

        } catch (\Exception $e) {
            Log::error('Erro na sincronização: ' . $e->getMessage());
            $this->syncLog->fail($e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->syncLog;
    }

    /**
     * Sincronizar colaboradores
     */
    protected function syncEmployees(array $options = []): void
    {
        $employees = $this->fetchEmployees($options['filters'] ?? []);
        $this->syncLog->update(['records_total' => count($employees)]);

        foreach ($employees as $employeeData) {
            try {
                $this->processEmployee($employeeData);
            } catch (\Exception $e) {
                $this->handleError('colaborador', $employeeData, $e);
            }
        }
    }

    /**
     * Sincronizar estrutura organizacional
     */
    protected function syncStructure(array $options = []): void
    {
        $structure = $this->fetchStructure();
        // Implementar lógica de sincronização de estrutura
    }

    /**
     * Sincronização completa
     */
    protected function syncComplete(array $options = []): void
    {
        $this->syncStructure($options);
        $this->syncEmployees($options);
    }

    /**
     * Processar colaborador individual
     */
    protected function processEmployee(array $data): void
    {
        // Aplicar mapeamento de campos
        $mappedData = $this->applyFieldMapping('colaborador', $data);

        // Validar dados
        if (!$this->validateEmployeeData($mappedData)) {
            $this->stats['skipped']++;
            $this->syncLog->incrementRecords('skipped');
            return;
        }

        // Buscar ou criar colaborador
        $colaborador = $this->findOrCreateEmployee($mappedData);

        if ($colaborador->wasRecentlyCreated) {
            $this->stats['created']++;
        } else {
            $this->stats['updated']++;
        }

        $this->syncLog->incrementRecords($colaborador->wasRecentlyCreated ? 'created' : 'updated');
    }

    /**
     * Aplicar mapeamento de campos
     */
    protected function applyFieldMapping(string $entityType, array $data): array
    {
        $fieldMappings = $this->integration->field_mapping ?? [];
        
        if (empty($fieldMappings)) {
            return $data;
        }

        $mapped = [];
        
        $relevantMappings = array_filter($fieldMappings, function($mapping) use ($entityType) {
            return ($mapping['entity_type'] ?? '') === $entityType;
        });

        foreach ($relevantMappings as $mapping) {
            $sourceField = $mapping['source_field'] ?? null;
            $targetField = $mapping['target_field'] ?? null;
            $isRequired = $mapping['is_required'] ?? false;
            $defaultValue = $mapping['default_value'] ?? null;
            
            if (!$sourceField || !$targetField) {
                continue;
            }

            $value = $data[$sourceField] ?? $defaultValue;
            
            if ($value !== null) {
                if (!empty($mapping['transformation'])) {
                    $value = $this->applyTransformation($value, $mapping['transformation']);
                }
                
                $mapped[$targetField] = $value;
            } elseif ($isRequired) {
                throw new \Exception("Campo obrigatório ausente: {$sourceField}");
            }
        }

        return $mapped;
    }

    /**
     * Aplicar transformação ao valor
     */
    protected function applyTransformation($value, string $transformation)
    {
        switch ($transformation) {
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'trim':
                return trim($value);
            case 'date':
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            default:
                return $value;
        }
    }

    /**
     * Validar dados do colaborador
     */
    protected function validateEmployeeData(array $data): bool
    {
        if (empty($data['matricula_funcional'] ?? null)) {
            Log::warning('Colaborador sem matrícula funcional, pulando...', ['data' => $data]);
            return false;
        }

        if (empty($data['nome_completo'] ?? null)) {
            Log::warning('Colaborador sem nome completo, pulando...', ['data' => $data]);
            return false;
        }

        return true;
    }

    /**
     * ✅ Buscar ou criar colaborador (BUSCA APENAS POR MATRÍCULA)
     */
    protected function findOrCreateEmployee(array $data)
    {
        $matricula = $data['matricula_funcional'] ?? null;

        if (!$matricula) {
            throw new \Exception('Matrícula funcional é obrigatória');
        }

        return DB::transaction(function() use ($data, $matricula) {
            // ✅ Buscar APENAS por matrícula funcional
            $colaborador = Colaborador::where('matricula_funcional', $matricula)->first();

            if ($colaborador) {
                // ATUALIZAR colaborador existente
                $updateData = $this->prepareColaboradorUpdate($data);
                $colaborador->update($updateData);
                $colaborador->wasRecentlyCreated = false;
                
                Log::info('✅ Colaborador atualizado via integração RH', [
                    'colaborador_id' => $colaborador->id,
                    'matricula' => $matricula,
                    'integration_id' => $this->integration->id
                ]);
            } else {
                // CRIAR novo colaborador
                $user = $this->findOrCreateUser($data);
                
                // ✅ Gerar senha provisória
                $senhaProvisoria = $this->generateProvisionalPassword($data['cpf'] ?? null);
                
                // Criar colaborador com senha provisória
                $createData = $this->prepareColaboradorCreate($data, $user, $senhaProvisoria);
                $colaborador = Colaborador::create($createData);
                $colaborador->wasRecentlyCreated = true;
                
                // ✅ Enviar email com senha provisória
                try {
                    $user->notify(new \App\Notifications\ProvisionalPasswordNotification($senhaProvisoria));
                    
                    Log::info('📧 Email com senha provisória enviado', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ Não foi possível enviar email com senha provisória', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                Log::info('✅ Colaborador criado via integração RH', [
                    'colaborador_id' => $colaborador->id,
                    'user_id' => $user->id,
                    'matricula' => $matricula,
                    'integration_id' => $this->integration->id,
                    'senha_provisoria_gerada' => true
                ]);
            }

            return $colaborador;
        });
    }

    /**
     * Preparar dados para UPDATE
     */
    protected function prepareColaboradorUpdate(array $data): array
    {
        return array_filter([
            'cargo' => $data['cargo'] ?? null,
            'status' => $this->mapStatus($data['status'] ?? null),
            'data_admissao' => $data['data_admissao'] ?? null,
            'email_funcional' => $data['email'] ?? $data['email_funcional'] ?? null,
            
            // Metadados da integração
            'hr_integration_id' => $this->integration->id,
            'external_id' => $data['external_id'] ?? $data['matricula_funcional'] ?? null,
            'hr_metadata' => $data,
            'synced_at' => now(),
            'updated_by' => auth()->id() ?? 1,
        ], fn($value) => $value !== null);
    }

    /**
     * ✅ Buscar ou criar usuário (SEM PASSWORD)
     */
    protected function findOrCreateUser(array $data): User
    {
        $cpf = $data['cpf'] ?? null;
        $email = $data['email'] ?? null;
        $nome = $data['nome_completo'] ?? $data['nome'] ?? 'Usuário Importado';
        
        if (!$cpf && !$email) {
            throw new \Exception('CPF ou Email é obrigatório para criar usuário');
        }
        
        // Buscar usuário existente
        $user = User::where(function($q) use ($cpf, $email) {
                if ($cpf) {
                    $q->where('cpf', $cpf);
                }
                if ($email) {
                    $q->orWhere('email', $email);
                }
            })
            ->first();
        
        if ($user) {
            Log::info('Usuário encontrado', ['user_id' => $user->id, 'cpf' => $cpf]);
            return $user;
        }
        
        // ✅ Criar usuário via DB direto (bypassa $fillable e password)
        $userId = DB::table('users')->insertGetId(array_filter([
            'name' => $nome,
            'cpf' => preg_replace('/\D/', '', $cpf), // ✅ Limpar CPF
            'email' => $email ?? "{$cpf}@temp.local",
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'telefone_principal' => $data['telefone'] ?? $data['telefone_principal'] ?? null,
            'endereco_completo' => $data['endereco'] ?? $data['endereco_completo'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], fn($val) => $val !== null));

        $user = User::find($userId);
        
        Log::info('Usuário criado via integração RH', ['user_id' => $user->id, 'cpf' => $cpf]);
        return $user;
    }

    /**
     * ✅ Preparar dados para CREATE (recebe senha provisória)
     */
    protected function prepareColaboradorCreate(array $data, User $user, string $senhaProvisoria): array
    {
        return [
            'user_id' => $user->id,
            'matricula_funcional' => $data['matricula_funcional'],
            'cpf' => preg_replace('/\D/', '', $data['cpf'] ?? ''), // ✅ Limpar CPF
            'nome_completo' => $data['nome_completo'] ?? $data['nome'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'email_funcional' => $data['email_funcional'] ?? $data['email'] ?? $user->email,
            
            // ✅ Senha provisória (hash)
            'password' => Hash::make($senhaProvisoria),
            'password_provisoria' => true, // ✅ Flag para forçar troca
            
            'cargo' => $data['cargo'] ?? 'Não informado',
            'data_admissao' => $data['data_admissao'] ?? now(),
            'status' => $this->mapStatus($data['status'] ?? 'ativo'),
            'is_gestor' => $data['is_gestor'] ?? false,
            
            // Campos de integração
            'hr_integration_id' => $this->integration->id,
            'external_id' => $data['external_id'] ?? $data['matricula_funcional'],
            'hr_metadata' => $data,
            'synced_at' => now(),
            
            // Auditoria
            'created_by' => auth()->id() ?? 1,
            'updated_by' => auth()->id() ?? 1,
        ];
    }

    /**
     * ✅ Gerar senha provisória segura
     */
    protected function generateProvisionalPassword(?string $cpf): string
    {
        if ($cpf) {
            // Pegar últimos 6 dígitos do CPF
            $cpfDigits = preg_replace('/\D/', '', $cpf);
            if (strlen($cpfDigits) >= 6) {
                return substr($cpfDigits, -6);
            }
        }
        
        // Fallback: senha padrão
        return 'Acad@' . date('Y');
    }

    /**
     * Mapear status externo para interno
     */
    protected function mapStatus(?string $externalStatus): string
    {
        $statusMap = [
            'ativo' => 'Ativo',
            'active' => 'Ativo',
            '1' => 'Ativo',
            'afastado' => 'Afastado',
            'inactive' => 'Desligado',
            'desligado' => 'Desligado',
            '0' => 'Desligado',
        ];
        
        $normalized = strtolower($externalStatus ?? 'ativo');
        return $statusMap[$normalized] ?? 'Ativo';
    }

    /**
     * Tratar erro de sincronização
     */
    protected function handleError(string $entityType, array $data, \Exception $e): void
    {
        $this->stats['failed']++;
        $this->syncLog->incrementRecords('failed');

        HRSyncError::create([
            'sync_log_id' => $this->syncLog->id,
            'entity_type' => $entityType,
            'entity_id' => $data['id'] ?? null,
            'error_message' => $e->getMessage(),
            'error_context' => $data
        ]);

        Log::error("Erro ao processar {$entityType}", [
            'data' => $data,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Fazer requisição HTTP com retry
     */
    protected function makeRequest(string $method, string $url, array $options = []): array
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders($this->getHeaders())
                    ->$method($url, $options);

                if ($response->successful()) {
                    return $response->json();
                }

                throw new \Exception("HTTP {$response->status()}: {$response->body()}");

            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                sleep(2 ** $attempts); // Exponential backoff
            }
        }

        throw new \Exception('Falha após múltiplas tentativas');
    }

    /**
     * Obter headers para requisições
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * ✅ Obter dados de exemplo (método padrão)
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
