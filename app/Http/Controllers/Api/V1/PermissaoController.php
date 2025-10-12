<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permissao;
use Illuminate\Http\JsonResponse;

class PermissaoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Permissao::all());
    }
}
