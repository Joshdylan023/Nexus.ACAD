<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Colaborador;

class ManagerSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        // Search Users by name
        $userIdsFromName = User::where('name', 'ILIKE', '%' . $query . '%')->pluck('id');

        // Search Colaborador by matricula_funcional
        $userIdsFromMatricula = Colaborador::where('matricula_funcional', 'ILIKE', '%' . $query . '%')->pluck('user_id');

        // Combine the user IDs
        $allUserIds = $userIdsFromName->merge($userIdsFromMatricula)->unique();

        // Get the users
        $users = User::whereIn('id', $allUserIds)
                    ->with('colaborador') // Eager load colaborador relationship
                    ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'matricula_funcional' => $user->colaborador ? $user->colaborador->matricula_funcional : null,
            ];
        });

        return response()->json($results);
    }
}