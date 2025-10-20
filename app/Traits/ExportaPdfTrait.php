<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

trait ExportaPdfTrait
{
    /**
     * Busca a identidade visual da instituição
     */
    protected function buscarIdentidadeVisual($instituicaoId = null, $entidade = null)
    {
        $identidadeVisual = null;
        $instituicao = null;

        if ($instituicaoId) {
            $instituicao = \App\Models\Instituicao::with('identidadeVisual')->find($instituicaoId);
            $identidadeVisual = $instituicao?->identidadeVisual;
        } else if ($entidade) {
            // Buscar instituição pela entidade relacionada
            $instituicao = $this->extrairInstituicao($entidade);
            $identidadeVisual = $instituicao?->identidadeVisual;
        }

        if (!$identidadeVisual && $instituicao) {
            $identidadeVisual = \App\Models\IdentidadeVisual::where('entidade_type', 'App\\Models\\Instituicao')
                ->where('entidade_id', $instituicao->id)
                ->first();
        }

        return [$identidadeVisual, $instituicao];
    }

    /**
     * Converte logo para base64
     */
    protected function converterLogoBase64($identidadeVisual)
    {
        if (!$identidadeVisual) {
            return null;
        }

        $logoField = $identidadeVisual->logo_principal 
            ?? $identidadeVisual->logo_horizontal 
            ?? $identidadeVisual->logo_icone;
        
        if (!$logoField) {
            return null;
        }

        $logoPath = storage_path('app/public/' . $logoField);
        
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            return 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
        }

        Log::warning('Logo não encontrado', [
            'path' => $logoPath,
            'logo_field' => $logoField,
        ]);

        return null;
    }

    /**
     * Extrai instituição de uma entidade relacionada
     */
    protected function extrairInstituicao($entidade)
    {
        if (!$entidade) {
            return null;
        }

        // Para prédios
        if (isset($entidade->campus)) {
            return $entidade->campus->instituicao ?? null;
        }

        // Para blocos e andares
        if (isset($entidade->predio)) {
            return $entidade->predio->campus->instituicao ?? null;
        }

        // Para espaços físicos
        if (isset($entidade->andar)) {
            return $entidade->andar->bloco->predio->campus->instituicao ?? null;
        }

        // Para reservas
        if (isset($entidade->espacoFisico)) {
            return $entidade->espacoFisico->andar->bloco->predio->campus->instituicao ?? null;
        }

        return null;
    }
}
