<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PerfilController extends Controller
{
    /**
     * Listar todos os perfis com suas permissões e contagem de usuários
     */
    public function index(): JsonResponse
    {
        $perfis = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => \DB::table('model_has_roles')
                        ->where('role_id', $role->id)
                        ->where('model_type', 'App\\Models\\User')
                        ->count(),
                    'permissions' => $role->permissions->pluck('name'),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ];
            });

        return response()->json($perfis);
    }

    /**
     * Mostrar um perfil específico
     */
    public function show($id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        // Buscar usuários com este perfil
        $userIds = \DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        $users = \App\Models\User::whereIn('id', $userIds)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'users' => $users,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ]);
    }

    /**
     * Criar novo perfil
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:perfis,name|max:255',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissoes,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Perfil criado com sucesso!',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')
            ]
        ], 201);
    }

    /**
     * Atualizar perfil
     */
    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:perfis,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissoes,name',
        ]);

        $role->update([
            'name' => $request->name
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')
            ]
        ]);
    }

    /**
     * Duplicar perfil
     */
    public function duplicate($id): JsonResponse
    {
        $originalRole = Role::with('permissions')->findOrFail($id);

        $newRole = Role::create([
            'name' => $originalRole->name . ' (Cópia)',
            'guard_name' => 'web'
        ]);

        $newRole->syncPermissions($originalRole->permissions->pluck('name'));

        return response()->json([
            'message' => 'Perfil duplicado com sucesso!',
            'data' => [
                'id' => $newRole->id,
                'name' => $newRole->name,
                'permissions' => $newRole->permissions->pluck('name')
            ]
        ], 201);
    }

    /**
     * Excluir perfil
     */
    public function destroy($id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Verificar se há usuários usando este perfil
        $usersCount = \DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', 'App\\Models\\User')
            ->count();

        if ($usersCount > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este perfil pois existem usuários associados.',
                'users_count' => $usersCount
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Perfil excluído com sucesso!'
        ]);
    }

    /**
     * Listar todas as permissões disponíveis (para o formulário)
     */
    public function getAvailablePermissions(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get()->map(function($perm) {
            return [
                'id' => $perm->id,
                'name' => $perm->name,
                'guard_name' => $perm->guard_name,
            ];
        });

        return response()->json($permissions);
    }
}
