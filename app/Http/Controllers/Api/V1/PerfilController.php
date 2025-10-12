<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Perfil::with('permissions')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:perfis,name|max:255',
            'descricao' => 'nullable|string',
        ]);

        $perfil = Perfil::create($validatedData);

        return response()->json($perfil, 201);
    }

    public function syncPermissoes(Request $request, Perfil $perfil): JsonResponse
    {
        $validatedData = $request->validate([
            'permissoes' => 'required|array',
            'permissoes.*' => 'exists:permissoes,id',
        ]);

        $perfil->syncPermissions($validatedData['permissoes']);

        return response()->json($perfil->load('permissions'));
    }
}
