<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Exibe uma lista de todos os usuários (colaboradores, alunos, docentes).
     * Para uso em selects e listagens internas do sistema.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('colaborador');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhereHas('colaborador', function($q2) use ($search) {
                      $q2->where('matricula_funcional', 'ILIKE', "%{$search}%")
                         ->orWhere('email_funcional', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $users = $query->limit(20)->get();
        return response()->json($users);
    }

    /**
     * Busca usuários por nome, email ou matrícula.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');
        
        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::with('colaborador')
            ->where(function($q) use ($query) {
                $q->where('name', 'ILIKE', "%{$query}%")
                  ->orWhere('email', 'ILIKE', "%{$query}%")
                  ->orWhereHas('colaborador', function($subQ) use ($query) {
                      $subQ->where('matricula_funcional', 'ILIKE', "%{$query}%");
                  });
            })
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'matricula' => $user->colaborador->matricula_funcional ?? 'N/A'
                ];
            });

        return response()->json($users);
    }

    /**
     * Lista apenas colaboradores ativos.
     * Útil para filtros específicos.
     */
    public function colaboradores(): JsonResponse
    {
        $colaboradores = User::with('colaborador')
            ->whereHas('colaborador')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $matricula = $user->colaborador->matricula_funcional ?? 'N/A';
                $emailFuncional = $user->colaborador->email_funcional ?? $user->email;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'matricula_funcional' => $matricula,
                    'email_funcional' => $emailFuncional,
                    'display_info' => sprintf(
                        '%s - %s (%s)',
                        $matricula,
                        $emailFuncional,
                        $user->name
                    )
                ];
            });

        return response()->json($colaboradores);
    }

    /**
     * Atualizar dados pessoais do usuário logado
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nome_social' => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'telefone_principal' => 'nullable|string|max:20',
            'telefone_secundario' => 'nullable|string|max:20',
            'endereco_completo' => 'nullable|string',
        ]);

        $user->update($validatedData);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user->load([
                'colaborador.setorVinculo.setor',
                'colaborador.unidadeOrganizacional',
                'colaborador.unidadeLotacao'
            ])
        ]);
    }

    /**
     * Alterar senha do usuário logado
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Verifica a senha atual no colaborador (onde a senha está armazenada)
        if (!$user->colaborador) {
            return response()->json([
                'message' => 'Usuário não é colaborador'
            ], 403);
        }

        if (!Hash::check($validatedData['current_password'], $user->colaborador->password)) {
            return response()->json([
                'message' => 'Senha atual incorreta'
            ], 422);
        }

        // Atualiza a senha no colaborador
        $user->colaborador->update([
            'password' => Hash::make($validatedData['new_password'])
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso!'
        ]);
    }

    /**
     * Determina o tipo do usuário baseado nos relacionamentos.
     */
    private function getUserType(User $user): string
    {
        if ($user->colaborador) {
            return 'colaborador';
        }
        // Preparado para futuro:
        // if ($user->aluno) return 'aluno';
        // if ($user->docente) return 'docente';
        
        return 'indefinido';
    }

    /**
     * Retorna informações específicas baseadas no tipo de usuário.
     */
    private function getSpecificUserInfo(User $user, string $type): array
    {
        switch ($type) {
            case 'colaborador':
                $matricula = $user->colaborador->matricula_funcional ?? 'N/A';
                $emailFuncional = $user->colaborador->email_funcional ?? $user->email;
                
                return [
                    'matricula' => $matricula,
                    'email_institucional' => $emailFuncional,
                    'display_info' => sprintf(
                        '%s - %s (%s)',
                        $matricula,
                        $emailFuncional,
                        $user->name
                    )
                ];

            // Preparado para futuro:
            /*
            case 'aluno':
                return [
                    'matricula' => $user->aluno->matricula ?? 'N/A',
                    'email_institucional' => $user->aluno->email_academico ?? $user->email,
                    'display_info' => sprintf(
                        '%s - %s (%s)',
                        $user->aluno->matricula ?? 'N/A',
                        $user->aluno->email_academico ?? $user->email,
                        $user->name
                    )
                ];

            case 'docente':
                return [
                    'matricula' => $user->docente->matricula ?? 'N/A',
                    'email_institucional' => $user->docente->email_institucional ?? $user->email,
                    'display_info' => sprintf(
                        '%s - %s (%s)',
                        $user->docente->matricula ?? 'N/A',
                        $user->docente->email_institucional ?? $user->email,
                        $user->name
                    )
                ];
            */

            default:
                return [
                    'matricula' => 'N/A',
                    'email_institucional' => $user->email,
                    'display_info' => sprintf('%s (%s)', $user->email, $user->name)
                ];
        }
    }

    /**
     * Upload de foto de perfil do colaborador
     */
    public function uploadProfilePhoto(Request $request)
    {
        $user = Auth::user();

        if (!$user->colaborador) {
            return response()->json([
                'message' => 'Usuário não é colaborador'
            ], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        try {
            // Remove foto antiga se existir
            if ($user->colaborador->foto_registro_rh) {
                Storage::disk('public')->delete($user->colaborador->foto_registro_rh);
            }

            // Salva nova foto
            $path = $request->file('photo')->store('fotos_colaboradores', 'public');

            // Atualiza no banco
            $user->colaborador->update([
                'foto_registro_rh' => $path
            ]);

            return response()->json([
                'message' => 'Foto atualizada com sucesso!',
                'photo_url' => "/storage/{$path}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao fazer upload da foto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover foto de perfil do colaborador
     */
    public function deleteProfilePhoto()
    {
        $user = Auth::user();

        if (!$user->colaborador) {
            return response()->json([
                'message' => 'Usuário não é colaborador'
            ], 403);
        }

        if ($user->colaborador->foto_registro_rh) {
            Storage::disk('public')->delete($user->colaborador->foto_registro_rh);
            
            $user->colaborador->update([
                'foto_registro_rh' => null
            ]);

            return response()->json([
                'message' => 'Foto removida com sucesso!'
            ]);
        }

        return response()->json([
            'message' => 'Nenhuma foto para remover'
        ], 404);
    }
}
