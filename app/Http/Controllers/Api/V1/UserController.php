<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Exibe uma lista de todos os usuários (colaboradores).
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email')->get();

        return response()->json($users);
    }
}
