<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IdentidadeVisual;
use App\Models\GrupoEducacional;
use App\Models\Mantenedora;
use App\Models\Instituicao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IdentidadeVisualController extends Controller
{
    /**
     * Buscar identidade visual de uma entidade
     */
    public function show(Request $request)
    {
        $entidadeType = $request->get('entidade_type');
        $entidadeId = $request->get('entidade_id');

        if (!$entidadeType || !$entidadeId) {
            return response()->json(['error' => 'Entidade não especificada'], 400);
        }

        $identidade = IdentidadeVisual::where('entidade_type', $entidadeType)
            ->where('entidade_id', $entidadeId)
            ->first();

        if ($identidade) {
            return response()->json($identidade);
        }

        // Se não tiver, buscar herdada
        $entidade = $this->getEntidade($entidadeType, $entidadeId);
        if ($entidade) {
            $identidadeAtiva = $entidade->getIdentidadeVisualAtiva();
            $origem = $entidade->getOrigemIdentidadeVisual();
            
            return response()->json([
                'identidade' => $identidadeAtiva,
                'origem' => $origem,
            ]);
        }

        return response()->json(['error' => 'Entidade não encontrada'], 404);
    }

    /**
     * Salvar ou atualizar identidade visual
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_type' => 'required|string',
            'entidade_id' => 'required|integer',
            'cor_primaria' => 'nullable|string|max:7',
            'cor_secundaria' => 'nullable|string|max:7',
            'cor_acento' => 'nullable|string|max:7',
            'cor_texto' => 'nullable|string|max:7',
            'fonte_principal' => 'nullable|string|max:100',
            'fonte_secundaria' => 'nullable|string|max:100',
            'usar_logo_documentos' => 'boolean',
            'usar_marca_dagua' => 'boolean',
            'posicao_logo' => 'nullable|in:topo-esquerda,topo-centro,topo-direita',
            'texto_rodape' => 'nullable|string',
            'site' => 'nullable|url|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $identidade = IdentidadeVisual::updateOrCreate(
                [
                    'entidade_type' => $validated['entidade_type'],
                    'entidade_id' => $validated['entidade_id'],
                ],
                array_merge($validated, [
                    'updated_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ])
            );

            DB::commit();

            return response()->json([
                'message' => 'Identidade visual salva com sucesso',
                'data' => $identidade,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload de logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'entidade_type' => 'required|string',
            'entidade_id' => 'required|integer',
            'tipo_logo' => 'required|in:logo_principal,logo_horizontal,logo_icone,logo_marca_dagua',
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        try {
            $identidade = IdentidadeVisual::firstOrCreate(
                [
                    'entidade_type' => $request->entidade_type,
                    'entidade_id' => $request->entidade_id,
                ],
                [
                    'created_by' => Auth::id(),
                ]
            );

            // Deletar logo antigo se existir
            $tipoLogo = $request->tipo_logo;
            if ($identidade->$tipoLogo) {
                Storage::disk('public')->delete($identidade->$tipoLogo);
            }

            // Salvar novo logo
            $path = $request->file('logo')->store('identidade-visual', 'public');
            $identidade->$tipoLogo = $path;
            $identidade->updated_by = Auth::id();
            $identidade->save();

            return response()->json([
                'message' => 'Logo enviado com sucesso',
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao fazer upload: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deletar logo
     */
    public function deleteLogo(Request $request)
    {
        $request->validate([
            'entidade_type' => 'required|string',
            'entidade_id' => 'required|integer',
            'tipo_logo' => 'required|in:logo_principal,logo_horizontal,logo_icone,logo_marca_dagua',
        ]);

        try {
            $identidade = IdentidadeVisual::where('entidade_type', $request->entidade_type)
                ->where('entidade_id', $request->entidade_id)
                ->first();

            if (!$identidade) {
                return response()->json(['error' => 'Identidade visual não encontrada'], 404);
            }

            $tipoLogo = $request->tipo_logo;
            if ($identidade->$tipoLogo) {
                Storage::disk('public')->delete($identidade->$tipoLogo);
                $identidade->$tipoLogo = null;
                $identidade->updated_by = Auth::id();
                $identidade->save();
            }

            return response()->json(['message' => 'Logo deletado com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Listar entidades disponíveis
     */
    public function listarEntidades()
    {
        return response()->json([
            'grupos' => GrupoEducacional::select('id', 'nome')->get(),
            'mantenedoras' => Mantenedora::select('id', 'razao_social as nome')->get(),
            'instituicoes' => Instituicao::select('id', 'razao_social as nome')->get(),
        ]);
    }

    /**
     * Helper para resolver entidade
     */
    private function getEntidade($type, $id)
    {
        return match($type) {
            'App\\Models\\GrupoEducacional' => GrupoEducacional::find($id),
            'App\\Models\\Mantenedora' => Mantenedora::find($id),
            'App\\Models\\Instituicao' => Instituicao::find($id),
            default => null,
        };
    }
}
