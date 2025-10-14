<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Todas as notificações marcadas como lidas']);
    }

    public function destroy(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json(['message' => 'Notificação excluída']);
    }

    // ============================================
    // NOVAS FUNCIONALIDADES: PREFERÊNCIAS
    // ============================================

    /**
     * Retorna as preferências de notificação do usuário
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();
        
        // Define as categorias disponíveis (customize conforme necessário)
        $categories = [
            'new_user' => 'Novos Usuários',
            'new_order' => 'Novos Pedidos',
            'payment' => 'Pagamentos',
            'task' => 'Tarefas',
            'comment' => 'Comentários',
            'system' => 'Sistema',
            'new_class' => 'Novas Turmas',
            'grade' => 'Notas',
            'attendance' => 'Frequência',
        ];

        $preferences = [];
        foreach ($categories as $key => $label) {
            $pref = NotificationPreference::firstOrCreate(
                ['user_id' => $user->id, 'category' => $key],
                [
                    'enabled' => true,
                    'email_enabled' => false,
                    'push_enabled' => true
                ]
            );

            $preferences[] = [
                'category' => $key,
                'label' => $label,
                'enabled' => $pref->enabled,
                'email_enabled' => $pref->email_enabled,
                'push_enabled' => $pref->push_enabled,
            ];
        }

        return response()->json($preferences);
    }

    /**
     * Atualiza as preferências de notificação do usuário
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.category' => 'required|string',
            'preferences.*.enabled' => 'required|boolean',
            'preferences.*.email_enabled' => 'required|boolean',
            'preferences.*.push_enabled' => 'required|boolean',
        ]);

        $user = $request->user();

        foreach ($validated['preferences'] as $pref) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category' => $pref['category']
                ],
                [
                    'enabled' => $pref['enabled'],
                    'email_enabled' => $pref['email_enabled'],
                    'push_enabled' => $pref['push_enabled'],
                ]
            );
        }

        return response()->json([
            'message' => 'Preferências atualizadas com sucesso'
        ]);
    }

    /**
     * Retorna estatísticas das notificações do usuário
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        $total = Notification::where('user_id', $user->id)->count();
        $unread = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        $byType = Notification::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        $byCategory = Notification::where('user_id', $user->id)
            ->selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        return response()->json([
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'by_type' => $byType,
            'by_category' => $byCategory,
        ]);
    }

    /**
     * Limpar todas as notificações lidas
     */
    public function clearRead(Request $request)
    {
        $deleted = Notification::where('user_id', $request->user()->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'message' => "{$deleted} notificações removidas com sucesso"
        ]);
    }
}
