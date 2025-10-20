<?php

namespace App\Notifications;

use App\Models\HRIntegration;
use App\Models\HRSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected HRIntegration $integration;
    protected ?HRSyncLog $syncLog;
    protected ?string $errorMessage;

    public function __construct(HRIntegration $integration, ?HRSyncLog $syncLog = null, ?string $errorMessage = null)
    {
        $this->integration = $integration;
        $this->syncLog = $syncLog;
        $this->errorMessage = $errorMessage;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("❌ Falha na Sincronização RH - {$this->integration->name}")
            ->greeting("Olá, {$notifiable->name}!")
            ->error()
            ->line("A sincronização com **{$this->integration->name}** falhou.");

        if ($this->syncLog) {
            $stats = $this->syncLog->summary;
            $mail->line('')
                ->line('**Estatísticas Parciais:**')
                ->line("• Criados: {$stats['created']}")
                ->line("• Atualizados: {$stats['updated']}")
                ->line("• Falhas: {$stats['failed']}")
                ->line('')
                ->line("**Erro:** {$this->syncLog->error_message}");
        } elseif ($this->errorMessage) {
            $mail->line('')
                ->line("**Erro:** {$this->errorMessage}");
        }

        $mail->action('Ver Detalhes', url("/integrations/{$this->integration->id}"))
            ->line('Por favor, verifique as configurações da integração.');

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'sync_failed',
            'integration_id' => $this->integration->id,
            'integration_name' => $this->integration->name,
            'sync_log_id' => $this->syncLog?->id,
            'error_message' => $this->syncLog?->error_message ?? $this->errorMessage,
            'message' => "Falha na sincronização com {$this->integration->name}",
            'icon' => 'exclamation-triangle',
            'color' => 'danger'
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
