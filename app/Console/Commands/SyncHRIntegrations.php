<?php

namespace App\Console\Commands;

use App\Models\HRIntegration;
use App\Jobs\SyncHRDataJob;
use Illuminate\Console\Command;

class SyncHRIntegrations extends Command
{
    protected $signature = 'hr:sync 
                            {--integration= : ID da integração específica}
                            {--type=colaboradores : Tipo de sincronização}
                            {--force : Forçar sincronização mesmo se desabilitada}';

    protected $description = 'Sincronizar dados de integrações RH';

    public function handle()
    {
        $integrationId = $this->option('integration');
        $syncType = $this->option('type');
        $force = $this->option('force');

        if ($integrationId) {
            // Sincronizar integração específica
            $integration = HRIntegration::findOrFail($integrationId);
            $this->syncIntegration($integration, $syncType, $force);
        } else {
            // Sincronizar todas as integrações ativas com sync automático
            $integrations = HRIntegration::active()
                ->where('auto_sync_enabled', true)
                ->get();

            if ($integrations->isEmpty()) {
                $this->warn('Nenhuma integração ativa com sincronização automática encontrada');
                return 0;
            }

            $this->info("Sincronizando {$integrations->count()} integrações...");

            foreach ($integrations as $integration) {
                $this->syncIntegration($integration, $syncType, $force);
            }
        }

        return 0;
    }

    protected function syncIntegration(HRIntegration $integration, string $syncType, bool $force)
    {
        if (!$force && !$integration->is_active) {
            $this->warn("Integração {$integration->name} está desabilitada");
            return;
        }

        $this->info("Agendando sincronização: {$integration->name}");

        SyncHRDataJob::dispatch($integration, $syncType)
            ->onQueue('hr-sync');

        $this->line("✓ Job agendado para: {$integration->name}");
    }
}
