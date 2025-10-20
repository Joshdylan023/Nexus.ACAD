<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HRIntegration;
use App\Models\HRSyncLog;
use App\Models\HRSyncError;
use App\Services\Integration\ConnectorFactory;
use App\Jobs\SyncHRDataJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IntegrationController extends Controller
{
    /**
     * Listar todas as integrações
     */
    public function index(): JsonResponse
    {
        try {
            $integrations = HRIntegration::with(['creator', 'lastSyncLog'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($integration) {
                    return [
                        'id' => $integration->id,
                        'name' => $integration->name,
                        'provider' => $integration->provider,
                        'provider_name' => $integration->provider_name,
                        'is_active' => $integration->is_active,
                        'auto_sync_enabled' => $integration->auto_sync_enabled ?? false,
                        'sync_frequency' => $integration->sync_frequency,
                        'last_sync_at' => $integration->last_sync_at?->toDateTimeString(),
                        'next_sync_at' => $integration->next_sync_at?->toDateTimeString(),
                        'last_sync_status' => $integration->last_sync_status,
                        'creator' => $integration->creator ? [
                            'id' => $integration->creator->id,
                            'name' => $integration->creator->name
                        ] : null,
                        'created_at' => $integration->created_at->toDateTimeString(),
                        'updated_at' => $integration->updated_at->toDateTimeString()
                    ];
                });

            return response()->json($integrations);
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar integrações: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao listar integrações',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar providers disponíveis
     */
    public function providers(): JsonResponse
    {
        try {
            $providers = ConnectorFactory::availableProviders();
            return response()->json($providers);
        } catch (\Exception $e) {
            Log::error('Erro ao listar providers: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao listar providers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter campos de configuração por provider
     */
    public function configFields(string $provider): JsonResponse
    {
        try {
            $fields = ConnectorFactory::getConfigFields($provider);
            
            if (empty($fields)) {
                return response()->json([
                    'error' => 'Provider não encontrado ou não configurado'
                ], 404);
            }
            
            return response()->json($fields);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar campos de configuração',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Criar nova integração
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => [
                'required',
                Rule::in(['generic', 'totvs', 'sap', 'oracle', 'senior', 'adp', 'adp_expert', 'csv'])
            ],
            'config' => 'required|array',
            'sync_frequency' => 'required|in:manual,hourly,daily,weekly',
            'is_active' => 'boolean',
            'field_mapping' => 'nullable|array',
            'sync_options' => 'nullable|array'
        ]);

        $validated['created_by'] = auth()->id() ?? 1;
        $validated['updated_by'] = auth()->id() ?? 1;

        try {
            $integration = HRIntegration::create($validated);
            
            // Calcular próxima sincronização se não for manual
            if ($integration->sync_frequency !== 'manual') {
                $integration->calculateNextSync();
                $integration->save();
            }

            Log::info('Integração criada', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'user_id' => auth()->id() ?? 1
            ]);

            return response()->json([
                'message' => 'Integração criada com sucesso',
                'integration' => $integration
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar integração: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao criar integração',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir detalhes da integração
     */
    public function show(HRIntegration $integration): JsonResponse
    {
        try {
            $integration->load([
                'creator', 
                'updater', 
                'lastSyncLog',
                'syncLogs' => function($q) {
                    $q->latest()->limit(10);
                }
            ]);

            // Estatísticas
            $stats = [
                'overview' => [
                    'total_syncs' => $integration->syncLogs()->count(),
                    'successful_syncs' => $integration->syncLogs()->where('status', 'completed')->count(),
                    'failed_syncs' => $integration->syncLogs()->where('status', 'failed')->count(),
                ],
                'records' => [
                    'total_created' => $integration->syncLogs()->sum('records_created'),
                    'total_updated' => $integration->syncLogs()->sum('records_updated'),
                    'total_failed' => $integration->syncLogs()->sum('records_failed'),
                ],
                'recent_syncs' => $integration->syncLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'status' => $log->status,
                        'records_total' => $log->records_total ?? 0,
                        'created_at' => $log->created_at->toDateTimeString(),
                        'duration' => $log->duration ?? 'N/A',
                    ];
                }),
            ];

            return response()->json([
                'integration' => $integration,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar integração: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao buscar integração',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar integração
     */
    public function update(Request $request, HRIntegration $integration): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'config' => 'array',
            'sync_frequency' => 'in:manual,hourly,daily,weekly',
            'is_active' => 'boolean',
            'field_mapping' => 'nullable|array',
            'sync_options' => 'nullable|array'
        ]);

        $validated['updated_by'] = auth()->id() ?? 1;

        try {
            $integration->update($validated);
            
            // Recalcular próxima sincronização se frequência mudou
            if (isset($validated['sync_frequency'])) {
                $integration->calculateNextSync();
                $integration->save();
            }

            Log::info('Integração atualizada', [
                'integration_id' => $integration->id,
                'user_id' => auth()->id() ?? 1
            ]);

            return response()->json([
                'message' => 'Integração atualizada com sucesso',
                'integration' => $integration->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar integração: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao atualizar integração',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir integração
     */
    public function destroy(HRIntegration $integration): JsonResponse
    {
        try {
            $integrationName = $integration->name;
            $integration->delete();

            Log::info('Integração excluída', [
                'integration_name' => $integrationName,
                'user_id' => auth()->id() ?? 1
            ]);

            return response()->json([
                'message' => 'Integração excluída com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir integração: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao excluir integração',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar conexão com o sistema externo
     */
    public function testConnection(HRIntegration $integration): JsonResponse
    {
        try {
            Log::info('Testando conexão', ['integration_id' => $integration->id]);
            
            $connector = ConnectorFactory::make($integration);
            $result = $connector->testConnection();

            Log::info('Resultado do teste de conexão', [
                'integration_id' => $integration->id,
                'success' => $result['success'] ?? false
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar dados
     */
    public function sync(Request $request, HRIntegration $integration): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|in:colaboradores,estrutura,completo',
            'async' => 'boolean',
            'filters' => 'nullable|array'
        ]);

        if (!$integration->is_active) {
            return response()->json([
                'error' => 'Integração está inativa',
                'message' => 'Ative a integração antes de sincronizar'
            ], 400);
        }

        try {
            $type = $validated['type'] ?? 'colaboradores';
            $async = $validated['async'] ?? true;
            $options = [
                'filters' => $validated['filters'] ?? [],
                'trigger_type' => 'manual'
            ];

            if ($async) {
                // Processar em fila
                SyncHRDataJob::dispatch($integration, $type, auth()->id() ?? 1, $options);
                
                Log::info('Sincronização agendada', [
                    'integration_id' => $integration->id,
                    'type' => $type,
                    'user_id' => auth()->id() ?? 1
                ]);
                
                return response()->json([
                    'message' => 'Sincronização iniciada em segundo plano',
                    'async' => true
                ]);
            } else {
                // Processar síncronamente
                $connector = ConnectorFactory::make($integration);
                $syncLog = $connector->sync($type, $options);

                return response()->json([
                    'message' => 'Sincronização concluída',
                    'sync_log' => $syncLog
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erro ao sincronizar',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar logs de sincronização
     */
    public function logs(Request $request, HRIntegration $integration): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');
        $type = $request->get('type');

        $query = $integration->syncLogs()
            ->with(['triggeredBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $logs = $query->latest()->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Detalhes de um log específico
     */
    public function logDetails(HRIntegration $integration, HRSyncLog $syncLog): JsonResponse
    {
        if ($syncLog->hr_integration_id !== $integration->id) {
            return response()->json([
                'error' => 'Log não pertence a esta integração'
            ], 403);
        }

        $syncLog->load(['triggeredBy', 'syncErrors']);

        return response()->json($syncLog);
    }

    /**
     * Estatísticas da integração
     */
    public function stats(HRIntegration $integration): JsonResponse
    {
        $stats = [
            'overview' => [
                'total_syncs' => $integration->syncLogs()->count(),
                'successful_syncs' => $integration->syncLogs()->where('status', 'completed')->count(),
                'failed_syncs' => $integration->syncLogs()->where('status', 'failed')->count(),
                'last_sync' => $integration->last_sync_at?->toDateTimeString(),
                'next_sync' => $integration->next_sync_at?->toDateTimeString()
            ],
            'records' => [
                'total_created' => $integration->syncLogs()->sum('records_created'),
                'total_updated' => $integration->syncLogs()->sum('records_updated'),
                'total_failed' => $integration->syncLogs()->sum('records_failed'),
                'total_skipped' => $integration->syncLogs()->sum('records_skipped')
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Ativar/Desativar integração
     */
    public function toggle(HRIntegration $integration): JsonResponse
    {
        try {
            $integration->update([
                'is_active' => !$integration->is_active,
                'updated_by' => auth()->id() ?? 1
            ]);

            $status = $integration->is_active ? 'ativada' : 'desativada';

            Log::info("Integração {$status}", [
                'integration_id' => $integration->id,
                'user_id' => auth()->id() ?? 1
            ]);

            return response()->json([
                'message' => "Integração {$status} com sucesso",
                'is_active' => $integration->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao alterar status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar configurações de agendamento
     */
    public function updateSchedule(Request $request, HRIntegration $integration): JsonResponse
    {
        $validated = $request->validate([
            'auto_sync_enabled' => 'required|boolean',
            'sync_frequency' => 'required|in:hourly,daily,weekly,monthly',
            'sync_time' => 'nullable|date_format:H:i',
            'sync_day' => 'nullable|integer|between:0,6',
        ]);

        $integration->update($validated);
        
        // Recalcular próxima sincronização
        $integration->calculateNextSync();
        $integration->save();

        return response()->json([
            'message' => 'Agendamento atualizado com sucesso',
            'integration' => $integration
        ]);
    }

    /**
     * ✅ Obter dados de exemplo para mapeamento
     */
    public function sampleData(HRIntegration $integration): JsonResponse
    {
        try {
            $connector = ConnectorFactory::make($integration);
            
            // Buscar 3-5 registros para visualização
            $sampleData = method_exists($connector, 'getSampleData') 
                ? $connector->getSampleData(3) 
                : array_slice($connector->fetchEmployees(), 0, 3);
            
            if (empty($sampleData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado encontrado no sistema externo',
                    'data' => [],
                    'fields' => []
                ], 404);
            }
            
            // Extrair campos disponíveis
            $fields = array_keys($sampleData[0]);
            
            return response()->json([
                'success' => true,
                'data' => $sampleData,
                'fields' => $fields
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados de exemplo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados de exemplo: ' . $e->getMessage(),
                'data' => [],
                'fields' => []
            ], 500);
        }
    }
}