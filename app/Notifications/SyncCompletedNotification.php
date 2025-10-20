<?php

namespace App\Notifications;

use App\Models\HRIntegration;
use App\Models\HRSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SyncCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected HRIntegration $integration;
    protected HRSyncLog $syncLog;

    public function __construct(HRIntegration $integration, HRSyncLog $syncLog)
    {
        $this->integration = $integration;
        $this->syncLog = $syncLog;
    }

    /**
     * Canais de notificação
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Notificação via email
     */
    public function toMail($notifiable): MailMessage
    {
        $stats = $this->syncLog->summary;

        return (new MailMessage)
            ->subject("✅ Sincronização RH Concluída - {$this->integration->name}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A sincronização com **{$this->integration->name}** foi concluída com sucesso.")
            ->line('')
            ->line('**Resumo da Sincronização:**')
            ->line("• Criados: {$stats['created']} colaboradores")
            ->line("• Atualizados: {$stats['updated']} colaboradores")
            ->line("• Falhas: {$stats['failed']}")
            ->line("• Ignorados: {$stats['skipped']}")
            ->line('')
            ->line("Duração: {$this->syncLog->duration}s")
            ->action('Ver Detalhes', url("/integrations/{$this->integration->id}/logs/{$this->syncLog->id}"))
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Notificação via banco de dados (in-app)
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'sync_completed',
            'integration_id' => $this->integration->id,
            'integration_name' => $this->integration->name,
            'sync_log_id' => $this->syncLog->id,
            'summary' => $this->syncLog->summary,
            'message' => "Sincronização com {$this->integration->name} concluída com sucesso",
            'icon' => 'check-circle',
            'color' => 'success'
        ];
    }

    /**
     * Array representation
     */
    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
