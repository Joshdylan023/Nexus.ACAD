<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Http\Request;

class CourseCoordinatorSearchController extends Controller
{
    /**
     * Search for users who are collaborators to be assigned as course coordinators.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([]);
        }

        // Search Users by name
        $useridsFromName = User::where('name', 'ILIKE', '%' . $query . '%')->pluck('id');

        // Search Colaborador by matricula_funcional
        $useridsFromMatricula = Colaborador::where('matricula_funcional', 'ILIKE', '%' . $query . '%')->pluck('user_id');

        // Combine the user IDs
        $colaboradorUserIds = $useridsFromName->merge($useridsFromMatricula)->unique();

        // Find users associated with these collaborators
        $users = User::whereIn('id', $colaboradorUserIds)
            ->with('colaborador') // Eager load colaborador relationship
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'matricula_funcional' => $user->colaborador ? $user->colaborador->matricula_funcional : null,
                ];
            });

        return response()->json($users);
    }
}
