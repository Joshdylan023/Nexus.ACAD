<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReservaRejeitadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reserva;
    protected $motivo;

    public function __construct($reserva, $motivo = null)
    {
        $this->reserva = $reserva;
        $this->motivo = $motivo;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reserva_rejeitada',
            'title' => 'Reserva Rejeitada', // ✅ CAMPO OBRIGATÓRIO
            'message' => "Sua reserva #{$this->reserva->id} foi rejeitada. Motivo: {$this->motivo}",
            'reserva_id' => $this->reserva->id,
            'espaco' => $this->reserva->espacoFisico->nome,
            'motivo' => $this->motivo,
            'icon' => 'x-circle',
            'color' => 'danger',
            'url' => "/reservas/{$this->reserva->id}"
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
