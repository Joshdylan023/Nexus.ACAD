<?php

namespace App\Jobs;

use App\Models\HRIntegration;
use App\Services\Integration\ConnectorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SyncCompletedNotification;
use App\Notifications\SyncFailedNotification;

class SyncHRDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3;
    public $backoff = 60; // Retry após 1 minuto

    protected HRIntegration $integration;
    protected string $syncType;
    protected ?int $triggeredBy;
    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(HRIntegration $integration, string $syncType = 'colaboradores', ?int $triggeredBy = null, array $options = [])
    {
        $this->integration = $integration;
        $this->syncType = $syncType;
        $this->triggeredBy = $triggeredBy;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando sincronização agendada', [
            'integration_id' => $this->integration->id,
            'integration_name' => $this->integration->name,
            'sync_type' => $this->syncType
        ]);

        try {
            // Criar connector
            $connector = ConnectorFactory::make($this->integration);

            // Executar sincronização
            $syncOptions = array_merge($this->options, [
                'triggered_by' => $this->triggeredBy
            ]);
            if (!isset($syncOptions['trigger_type'])) {
                $syncOptions['trigger_type'] = 'scheduled';
            }
            $syncLog = $connector->sync($this->syncType, $syncOptions);

            // Notificar sucesso
            if ($syncLog->status === 'completed') {
                $this->notifySuccess($syncLog);
            } else {
                $this->notifyFailure($syncLog);
            }

            Log::info('Sincronização agendada concluída', [
                'sync_log_id' => $syncLog->id,
                'status' => $syncLog->status
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na sincronização agendada', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->notifyError($e);
            
            throw $e; // Re-throw para o Laravel Queue handle retry
        }
    }

    /**
     * Notificar sucesso
     */
    protected function notifySuccess($syncLog): void
    {
        $admins = $this->getAdmins();
        
        Notification::send($admins, new SyncCompletedNotification(
            $this->integration,
            $syncLog
        ));
    }

    /**
     * Notificar falha
     */
    protected function notifyFailure($syncLog): void
    {
        $admins = $this->getAdmins();
        
        Notification::send($admins, new SyncFailedNotification(
            $this->integration,
            $syncLog
        ));
    }

    /**
     * Notificar erro
     */
    protected function notifyError(\Exception $e): void
    {
        $admins = $this->getAdmins();
        
        Notification::send($admins, new SyncFailedNotification(
            $this->integration,
            null,
            $e->getMessage()
        ));
    }

    /**
     * Obter administradores para notificar
     */
    protected function getAdmins()
    {
        return \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->get();
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de sincronização falhou após todas as tentativas', [
            'integration_id' => $this->integration->id,
            'error' => $exception->getMessage()
        ]);

        $this->notifyError($exception);
    }
}
