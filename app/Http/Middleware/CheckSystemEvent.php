<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemEvent;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemEvent
{
    public function handle(Request $request, Closure $next, string $portalType = null): Response
    {
        $event = SystemEvent::getActiveEvent();

        if (!$event) {
            return $next($request);
        }

        $user = $request->user();

        // Verifica se usuário tem permissão de acesso durante o evento
        if ($user && $event->canUserAccess($user->id)) {
            return $next($request);
        }

        // Bloqueia acesso baseado no tipo de portal
        $blocked = false;
        $message = 'Sistema em manutenção';

        switch ($portalType) {
            case 'student':
                $blocked = $event->block_student_portal;
                $message = 'Portal do Aluno temporariamente indisponível';
                break;
            case 'teacher':
                $blocked = $event->block_teacher_portal;
                $message = 'Portal do Docente temporariamente indisponível';
                break;
            case 'admin':
                $blocked = $event->block_admin_portal;
                $message = 'Acesso administrativo bloqueado durante manutenção';
                break;
        }

        if ($blocked) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'event' => [
                        'title' => $event->title,
                        'description' => $event->description,
                        'end_at' => $event->end_at
                    ]
                ], 503);
            }

            abort(503, $message);
        }

        return $next($request);
    }
}
