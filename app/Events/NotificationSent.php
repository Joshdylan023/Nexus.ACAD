<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Notification $notification;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification, $userId)
    {
        $this->notification = $notification; // A propriedade já é pública, mas vamos adicionar o type hint para clareza
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name (removido para usar o nome da classe).
     * Se descomentar, o evento será 'notification.sent' em vez de 'NotificationSent'
     */
    // public function broadcastAs(): string
    // {
    //     return 'notification.sent';
    // }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'title' => $this->notification->title,
                'message' => $this->notification->message,
                'created_at' => $this->notification->created_at->toISOString(),
            ]
        ];
    }
}
