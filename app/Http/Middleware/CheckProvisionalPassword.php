<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProvisionalPassword
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user && $user->colaborador && $user->colaborador->password_provisoria) {
            // Se não está na rota de trocar senha, redirecionar
            if (!$request->routeIs('password.change')) {
                return redirect()->route('password.change')
                    ->with('warning', 'Você precisa alterar sua senha provisória antes de continuar.');
            }
        }
        
        return $next($request);
    }
}
