<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'grupos' => [],
                'mantenedoras' => [],
                'instituicoes' => [],
                'campi' => [],
                'setores' => [],
                'users' => [],
            ]);
        }

        $searchTerm = '%' . $query . '%';
        $results = [];

        // ⭐ GRUPOS - COM ILIKE (CASE-INSENSITIVE)
        try {
            $results['grupos'] = DB::table('grupos_educacionais')
                ->where('nome', 'ILIKE', $searchTerm)
                ->orWhere('cnpj', 'ILIKE', $searchTerm)
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'nome', 'cnpj']);
        } catch (\Exception $e) {
            $results['grupos'] = [];
        }

        // ⭐ MANTENEDORAS - COM ILIKE
        try {
            $results['mantenedoras'] = DB::table('mantenedoras')
                ->where(function($q) use ($searchTerm) {
                    $q->where('razao_social', 'ILIKE', $searchTerm)
                      ->orWhere('nome_fantasia', 'ILIKE', $searchTerm)
                      ->orWhere('cnpj', 'ILIKE', $searchTerm);
                })
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'razao_social', 'nome_fantasia', 'cnpj', 'grupo_educacional_id']);
        } catch (\Exception $e) {
            $results['mantenedoras'] = [];
        }

        // ⭐ INSTITUIÇÕES - COM ILIKE
        try {
            $results['instituicoes'] = DB::table('instituicoes')
                ->where(function($q) use ($searchTerm) {
                    $q->where('razao_social', 'ILIKE', $searchTerm)
                      ->orWhere('nome_fantasia', 'ILIKE', $searchTerm)
                      ->orWhere('sigla', 'ILIKE', $searchTerm);
                })
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'razao_social', 'nome_fantasia', 'sigla', 'cidade', 'estado', 'mantenedora_id']);
        } catch (\Exception $e) {
            $results['instituicoes'] = [];
        }

        // ⭐ CAMPI - COM ILIKE
        try {
            $results['campi'] = DB::table('campi')
                ->where('nome', 'ILIKE', $searchTerm)
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'nome', 'instituicao_id', 'endereco_completo']);
        } catch (\Exception $e) {
            $results['campi'] = [];
        }

        // ⭐ SETORES - COM ILIKE
        try {
            $results['setores'] = DB::table('setores')
                ->where('nome', 'ILIKE', $searchTerm)
                ->whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'nome']);
        } catch (\Exception $e) {
            $results['setores'] = [];
        }

        // ⭐ USUÁRIOS - COM ILIKE
        try {
            $results['users'] = DB::table('users')
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'ILIKE', $searchTerm)
                      ->orWhere('email', 'ILIKE', $searchTerm);
                })
                ->limit(5)
                ->get(['id', 'name', 'email']);
        } catch (\Exception $e) {
            $results['users'] = [];
        }

        return response()->json($results);
    }
}
