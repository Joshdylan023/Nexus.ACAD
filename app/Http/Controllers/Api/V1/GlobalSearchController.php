<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use App\Models\Campus;
use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [
            'grupos_educacionais' => $this->searchGruposEducacionais($query),
            'mantenedoras' => $this->searchMantenedoras($query),
            'instituicoes' => $this->searchInstituicoes($query),
            'campi' => $this->searchCampi($query),
            'cursos' => $this->searchCursos($query),
            'disciplinas' => $this->searchDisciplinas($query),
            'colaboradores' => $this->searchColaboradores($query)
        ];

        return response()->json($results);
    }

    private function searchGruposEducacionais($query)
    {
        return GrupoEducacional::where('razao_social', 'ILIKE', "%{$query}%")
            ->orWhere('nome_fantasia', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'razao_social', 'nome_fantasia'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->razao_social,
                    'subtitle' => $item->nome_fantasia,
                    'type' => 'grupo_educacional',
                    'url' => "/admin/institucional/grupos-educacionais/{$item->id}"
                ];
            });
    }

    private function searchMantenedoras($query)
    {
        return Mantenedora::where('razao_social', 'ILIKE', "%{$query}%")
            ->orWhere('nome_fantasia', 'ILIKE', "%{$query}%")
            ->orWhere('cnpj', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->razao_social,
                    'subtitle' => $item->cnpj,
                    'type' => 'mantenedora',
                    'url' => "/admin/institucional/mantenedoras/{$item->id}"
                ];
            });
    }

    private function searchInstituicoes($query)
    {
        return Instituicao::where('razao_social', 'ILIKE', "%{$query}%")
            ->orWhere('sigla', 'ILIKE', "%{$query}%")
            ->orWhere('codigo_mec', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'razao_social', 'sigla', 'codigo_mec'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->razao_social,
                    'subtitle' => "Código MEC: {$item->codigo_mec}",
                    'type' => 'instituicao',
                    'url' => "/admin/institucional/instituicoes/{$item->id}"
                ];
            });
    }

    private function searchCampi($query)
    {
        return Campus::with('instituicao')
            ->where('nome', 'ILIKE', "%{$query}%")
            ->orWhere('cidade', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'nome', 'cidade', 'instituicao_id'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nome,
                    'subtitle' => "{$item->cidade} - {$item->instituicao->razao_social}",
                    'type' => 'campus',
                    'url' => "/admin/institucional/campi/{$item->id}"
                ];
            });
    }

    private function searchCursos($query)
    {
        return Curso::where('nome', 'ILIKE', "%{$query}%")
            ->orWhere('codigo', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'nome', 'codigo', 'grau_academico'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nome,
                    'subtitle' => "{$item->grau_academico} - {$item->codigo}",
                    'type' => 'curso',
                    'url' => "/admin/academico/cursos/{$item->id}"
                ];
            });
    }

    private function searchDisciplinas($query)
    {
        return Disciplina::where('nome', 'ILIKE', "%{$query}%")
            ->orWhere('codigo', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'nome', 'codigo', 'carga_horaria'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nome,
                    'subtitle' => "{$item->codigo} - {$item->carga_horaria}h",
                    'type' => 'disciplina',
                    'url' => "/admin/academico/disciplinas/{$item->id}"
                ];
            });
    }

    private function searchColaboradores($query)
    {
        return User::where('name', 'ILIKE', "%{$query}%")
            ->orWhere('email', 'ILIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'subtitle' => $item->email,
                    'type' => 'colaborador',
                    'url' => "/admin/acessos/colaboradores/{$item->id}"
                ];
            });
    }
}
