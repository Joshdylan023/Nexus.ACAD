<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReservaAprovadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reserva;

    public function __construct($reserva)
    {
        $this->reserva = $reserva;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reserva_aprovada',
            'title' => 'Reserva Aprovada', // ✅ CAMPO OBRIGATÓRIO
            'message' => "Sua reserva #{$this->reserva->id} para {$this->reserva->espacoFisico->nome} foi aprovada!",
            'reserva_id' => $this->reserva->id,
            'espaco' => $this->reserva->espacoFisico->nome,
            'data' => $this->reserva->data_inicio,
            'hora' => $this->reserva->hora_inicio,
            'icon' => 'check-circle',
            'color' => 'success',
            'url' => "/reservas/{$this->reserva->id}"
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
