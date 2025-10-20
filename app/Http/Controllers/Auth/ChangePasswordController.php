<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $colaborador = Auth::user()->colaborador;

        if (!$colaborador) {
            return back()->withErrors(['error' => 'Colaborador não encontrado']);
        }

        // Verificar senha atual
        if (!Hash::check($request->current_password, $colaborador->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta']);
        }

        // Atualizar senha
        $colaborador->update([
            'password' => Hash::make($request->password),
            'password_provisoria' => false, // ✅ Remover flag
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Senha alterada com sucesso!');
    }
}
