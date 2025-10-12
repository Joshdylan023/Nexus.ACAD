<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Usaremos o vínculo de Colaborador para o login
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Lida com a tentativa de autenticação para o portal administrativo.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'matricula' => 'required|string',
            'password' => 'required|string',
        ]);

        // 1. Procura o VÍNCULO de colaborador pela matrícula funcional
        $colaborador = Colaborador::where('matricula_funcional', $request->matricula)->first();

        // 2. Verifica se o vínculo existe e se a senha do VÍNCULO está correta
        if (! $colaborador || ! Hash::check($request->password, $colaborador->password)) {
            return response()->json(['message' => 'Matrícula ou senha inválida.'], 401);
        }

        // 3. Se as credenciais estiverem corretas, pega a "Pessoa" (User) associada
        $user = $colaborador->usuario;

        // 4. Cria um token de acesso para a "Pessoa"
        $user->tokens()->delete();
        $token = $user->createToken('auth_token_nexus_admin')->plainTextToken;

        return response()->json([
            'message' => 'Login bem-sucedido!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
