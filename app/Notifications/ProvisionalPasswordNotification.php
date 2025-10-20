<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProvisionalPasswordNotification extends Notification
{
    protected string $provisionalPassword;

    public function __construct(string $provisionalPassword)
    {
        $this->provisionalPassword = $provisionalPassword;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao Sistema - Senha Provisória')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Seu cadastro foi criado no sistema.')
            ->line("**Sua senha provisória é:** `{$this->provisionalPassword}`")
            ->line('Por segurança, você será solicitado a alterar sua senha no primeiro acesso.')
            ->action('Acessar Sistema', url('/login'))
            ->line('Obrigado!');
    }
}
