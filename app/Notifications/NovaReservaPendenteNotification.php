<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NovaReservaPendenteNotification extends Notification implements ShouldQueue
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

    // ✅ ADICIONAR O MÉTODO toArray COM TITLE
    public function toArray($notifiable)
    {
        return [
            'type' => 'reserva_pendente',
            'title' => 'Nova Reserva Pendente', // ✅ ADICIONAR AQUI
            'message' => "{$this->reserva->solicitante->name} solicitou uma reserva para {$this->reserva->espacoFisico->nome}",
            'reserva_id' => $this->reserva->id,
            'solicitante' => $this->reserva->solicitante->name,
            'espaco' => $this->reserva->espacoFisico->nome,
            'icon' => 'clock',
            'color' => 'warning',
            'url' => "/reservas/{$this->reserva->id}"
        ];
    }

    // ✅ BROADCAST MESSAGE
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'reserva_pendente',
            'title' => 'Nova Reserva Pendente',
            'message' => "{$this->reserva->solicitante->name} solicitou uma reserva para {$this->reserva->espacoFisico->nome}",
            'reserva_id' => $this->reserva->id,
            'solicitante' => $this->reserva->solicitante->name,
            'espaco' => $this->reserva->espacoFisico->nome,
            'icon' => 'clock',
            'color' => 'warning',
            'url' => "/reservas/{$this->reserva->id}"
        ]);
    }
}
