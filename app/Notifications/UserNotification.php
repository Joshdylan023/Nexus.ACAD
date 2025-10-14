<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $notificationData;

    public function __construct(array $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->notificationData['title'])
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->notificationData['message']);

        if (isset($this->notificationData['action_url'])) {
            $mail->action('Ver Detalhes', url($this->notificationData['action_url']));
        }

        return $mail
            ->line('Obrigado por usar nosso sistema!')
            ->salutation('Atenciosamente, Equipe ' . config('app.name'));
    }

    public function toArray($notifiable): array
    {
        return $this->notificationData;
    }
}
