<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Colaborador;
use App\Models\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GestaoAcessosController extends Controller
{
    /**
     * Obter permissões do usuário
     */
    public function getUserPermissions(User $user): JsonResponse
    {
        return response()->json([
            'roles' => $user->roles,
            'permissions_from_roles' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }

    /**
     * Atribuir perfis ao usuário
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'string|exists:perfis,name',
        ]);

        $user->syncRoles($request->roles ?? []);

        return response()->json([
            'message' => 'Perfis atualizados com sucesso!',
            'roles' => $user->roles
        ]);
    }

    /**
     * Conceder permissão direta
     */
    public function grantPermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string|exists:permissoes,name',
        ]);

        $user->givePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permissão concedida com sucesso!',
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    /**
     * Revogar permissão direta
     */
    public function revokePermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string|exists:permissoes,name',
        ]);

        $user->revokePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permissão revogada com sucesso!',
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    /**
     * Sincronizar permissões diretas
     */
    public function syncDirectPermissions(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissoes,name',
        ]);

        $user->permissions()->detach();

        foreach ($request->permissions ?? [] as $permission) {
            $user->givePermissionTo($permission);
        }

        return response()->json([
            'message' => 'Permissões sincronizadas com sucesso!',
            'direct_permissions' => $user->getDirectPermissions()->pluck('name')
        ]);
    }

    /**
     * Limpar permissões diretas
     */
    public function clearDirectPermissions(User $user): JsonResponse
    {
        $user->permissions()->detach();

        return response()->json([
            'message' => 'Permissões diretas removidas com sucesso!',
            'permissions' => $user->getPermissionsViaRoles()->pluck('name')
        ]);
    }

    /**
     * ⭐ Obter instituições com acesso do colaborador (COM ROLES E PERMISSIONS)
     */
    public function getColaboradorInstituicoes($colaboradorId): JsonResponse
    {
        $colaborador = Colaborador::with(['instituicoesAcesso' => function($query) {
            $query->select('instituicoes.id', 'instituicoes.nome_fantasia', 'instituicoes.sigla');
        }])->findOrFail($colaboradorId);

        $instituicoes = $colaborador->instituicoesAcesso->map(function($inst) {
            return [
                'id' => $inst->id,
                'nome' => $inst->nome_fantasia,
                'sigla' => $inst->sigla,
                'roles' => $inst->pivot->roles ?? [],
                'permissions' => $inst->pivot->permissions ?? [],
            ];
        });

        return response()->json([
            'instituicoes' => $instituicoes
        ]);
    }

    /**
     * ⭐ Atribuir instituições ao colaborador
     */
    public function assignInstituicoes(Request $request, $colaboradorId): JsonResponse
    {
        $request->validate([
            'instituicoes' => 'required|array',
            'instituicoes.*' => 'exists:instituicoes,id',
        ]);

        $colaborador = Colaborador::findOrFail($colaboradorId);
        $colaborador->instituicoesAcesso()->sync($request->instituicoes);

        // Recarregar com os dados formatados
        $instituicoes = $colaborador->instituicoesAcesso()
            ->select('instituicoes.id', 'instituicoes.nome_fantasia', 'instituicoes.sigla')
            ->get()
            ->map(function($inst) {
                return [
                    'id' => $inst->id,
                    'nome' => $inst->nome_fantasia,
                    'sigla' => $inst->sigla,
                    'roles' => $inst->pivot->roles ?? [],
                    'permissions' => $inst->pivot->permissions ?? [],
                ];
            });

        return response()->json([
            'message' => 'Instituições atualizadas com sucesso!',
            'instituicoes' => $instituicoes
        ]);
    }

    /**
     * ⭐ Listar todas as instituições disponíveis
     */
    public function listInstituicoes(): JsonResponse
    {
        $instituicoes = Instituicao::select('id', 'nome_fantasia', 'sigla')
            ->orderBy('nome_fantasia')
            ->get()
            ->map(function($inst) {
                return [
                    'id' => $inst->id,
                    'nome' => $inst->nome_fantasia,
                    'sigla' => $inst->sigla
                ];
            });

        return response()->json($instituicoes);
    }

    /**
     * ⭐ Obter roles e permissões de uma instituição específica
     */
    public function getInstituicaoAcessos($colaboradorId, $instituicaoId): JsonResponse
    {
        $colaborador = Colaborador::findOrFail($colaboradorId);
        
        return response()->json([
            'roles' => $colaborador->getRolesForInstituicao($instituicaoId),
            'permissions' => $colaborador->getPermissionsForInstituicao($instituicaoId),
        ]);
    }

    /**
     * ⭐ Atribuir roles a uma instituição específica
     */
    public function assignRolesToInstituicao(Request $request, $colaboradorId, $instituicaoId): JsonResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'string',
        ]);

        $colaborador = Colaborador::findOrFail($colaboradorId);
        
        // Se não enviar roles, assume array vazio
        $roles = $request->roles ?? [];

        $colaborador->syncRolesForInstituicao($instituicaoId, $roles);

        return response()->json([
            'message' => 'Perfis atualizados com sucesso!',
            'roles' => $colaborador->getRolesForInstituicao($instituicaoId)
        ]);
    }

    /**
     * ⭐ Atribuir permissões a uma instituição específica
     */
    public function assignPermissionsToInstituicao(Request $request, $colaboradorId, $instituicaoId): JsonResponse
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $colaborador = Colaborador::findOrFail($colaboradorId);
        
        // Se não enviar permissões, assume array vazio
        $permissions = $request->permissions ?? [];
        
        $colaborador->syncPermissionsForInstituicao($instituicaoId, $permissions);

        return response()->json([
            'message' => 'Permissões atualizadas com sucesso!',
            'permissions' => $colaborador->getPermissionsForInstituicao($instituicaoId)
        ]);
    }
}
