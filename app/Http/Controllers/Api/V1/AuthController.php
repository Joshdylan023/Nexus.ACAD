<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        if (!$colaborador || !Hash::check($request->password, $colaborador->password)) {
            return response()->json(['message' => 'Matrícula ou senha inválida.'], 401);
        }

        // 3. Se as credenciais estiverem corretas, pega a "Pessoa" (User) associada
        $user = $colaborador->usuario;

        // 4. Cria um token de acesso para a "Pessoa"
        $user->tokens()->delete();
        $token = $user->createToken('auth_token_nexus_admin')->plainTextToken;

        // ⭐ 5. OBTER PERMISSÕES E ROLES
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'message' => 'Login bem-sucedido!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'data_nascimento' => $user->data_nascimento,
                // ⭐ ADICIONAR PERMISSÕES E ROLES
                'permissions' => $permissions,
                'roles' => $roles,
                // ⭐ DADOS DO COLABORADOR (se necessário)
                'colaborador' => [
                    'id' => $colaborador->id,
                    'matricula_funcional' => $colaborador->matricula_funcional,
                    'tipo_colaborador' => $colaborador->tipo_colaborador,
                    'data_admissao' => $colaborador->data_admissao,
                ],
            ],
        ]);
    }

    /**
     * ⭐ LOGOUT (opcional, mas recomendado)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso!'
        ]);
    }

    /**
     * ⭐ OBTER USUÁRIO AUTENTICADO (com permissões atualizadas)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(
            'colaborador.setorVinculo.setor',
        );

        // O método load() já carrega as relações no objeto $user.
        // Agora, vamos construir a resposta manualmente para garantir que
        // as permissões e papéis também sejam incluídos no nível raiz.

        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name')->toArray();
        $userData['roles'] = $user->getRoleNames()->toArray();

        return response()->json($userData);
    }
}
