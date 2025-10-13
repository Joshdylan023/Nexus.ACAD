<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Exibe uma lista de todos os usuários (colaboradores, alunos, docentes).
     * Para uso em selects e listagens internas do sistema.
     */
    public function index(): JsonResponse
    {
        $users = User::with(['colaborador'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $userType = $this->getUserType($user);
                $specificInfo = $this->getSpecificUserInfo($user, $userType);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $userType,
                    'matricula' => $specificInfo['matricula'],
                    'email_institucional' => $specificInfo['email_institucional'],
                    'display_info' => $specificInfo['display_info']
                ];
            });

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
}
