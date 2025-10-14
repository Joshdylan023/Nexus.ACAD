<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Events\NotificationSent;
use Illuminate\Support\Collection;

class NotificationHelper
{
    /**
     * Notificar múltiplos usuários
     */
    public static function notifyUsers(
        array|Collection $userIds,
        string $type,
        string $title,
        string $message,
        ?string $category = null,
        ?string $actionUrl = null,
        ?array $data = null,
        bool $sendEmail = false
    ): void
    {
        $userIds = is_array($userIds) ? collect($userIds) : $userIds;

        $userIds->each(function ($userId) use ($type, $title, $message, $category, $actionUrl, $data, $sendEmail) {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'category' => $category,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'data' => $data,
            ]);

            // Broadcast WebSocket
            broadcast(new NotificationSent($notification, $userId));

            // Enviar e-mail se solicitado
            if ($sendEmail) {
                $user = User::find($userId);
                if ($user && $user->email) {
                    $user->notify(new \App\Notifications\UserNotification([
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'action_url' => $actionUrl,
                    ]));
                }
            }
        });
    }

    /**
     * Notificar todos os usuários com determinada role (Spatie Permission)
     */
    public static function notifyByRole(
        string $roleName,
        string $type,
        string $title,
        string $message,
        ?string $category = null,
        ?string $actionUrl = null,
        bool $sendEmail = false
    ): void
    {
        $userIds = User::role($roleName)->pluck('id');
        self::notifyUsers($userIds, $type, $title, $message, $category, $actionUrl, null, $sendEmail);
    }

    /**
     * Notificar todos os usuários
     */
    public static function notifyAll(
        string $type,
        string $title,
        string $message,
        ?string $category = null,
        ?string $actionUrl = null,
        bool $sendEmail = false
    ): void
    {
        $userIds = User::pluck('id');
        self::notifyUsers($userIds, $type, $title, $message, $category, $actionUrl, null, $sendEmail);
    }

    /**
     * Notificar baseado em preferências do usuário
     */
    public static function notifyWithPreferences(
        array|Collection $userIds,
        string $category,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $data = null
    ): void
    {
        $userIds = is_array($userIds) ? collect($userIds) : $userIds;

        // Filtrar usuários que aceitam este tipo de notificação
        $filteredUserIds = $userIds->filter(function ($userId) use ($category) {
            $prefs = \App\Models\NotificationPreference::where('user_id', $userId)
                ->where('category', $category)
                ->first();
            
            return !$prefs || $prefs->enabled; // Se não tem preferência, aceita
        });

        self::notifyUsers($filteredUserIds, $type, $title, $message, $category, $actionUrl, $data);
    }
}
