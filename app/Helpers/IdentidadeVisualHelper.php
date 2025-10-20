<?php

namespace App\Helpers;

use App\Models\IdentidadeVisual;

class IdentidadeVisualHelper
{
    /**
     * Busca a identidade visual de uma instituição
     * Se não encontrar, busca a do grupo educacional
     */
    public static function buscarPorInstituicao($instituicao)
    {
        if (!$instituicao) {
            return null;
        }

        // Buscar identidade da instituição
        $identidade = IdentidadeVisual::where('entidade_type', 'App\Models\Instituicao')
            ->where('entidade_id', $instituicao->id)
            ->first();

        // Se não encontrar, buscar do grupo educacional
        if (!$identidade && $instituicao->grupo_educacional_id) {
            $identidade = IdentidadeVisual::where('entidade_type', 'App\Models\GrupoEducacional')
                ->where('entidade_id', $instituicao->grupo_educacional_id)
                ->first();
        }

        return $identidade;
    }
}
