<?php

namespace App\Traits;

use App\Models\IdentidadeVisual;

trait HasIdentidadeVisual
{
    /**
     * Relacionamento com Identidade Visual
     */
    public function identidadeVisual()
    {
        return $this->morphOne(IdentidadeVisual::class, 'entidade');
    }

    /**
     * Resolve identidade visual com herança
     */
    public function getIdentidadeVisualAtiva()
    {
        if ($this->identidadeVisual) {
            return $this->identidadeVisual;
        }

        if ($this instanceof \App\Models\Instituicao && $this->mantenedora?->getIdentidadeVisualAtiva()) {
            return $this->mantenedora->getIdentidadeVisualAtiva();
        }

        if ($this instanceof \App\Models\Mantenedora && $this->grupoEducacional?->identidadeVisual) {
            return $this->grupoEducacional->identidadeVisual;
        }

        return $this->getIdentidadePadrao();
    }

    /**
     * Identidade visual padrão do sistema
     */
    private function getIdentidadePadrao()
    {
        return (object) [
            'logo_principal' => asset('images/logo-nexus-acad.png'),
            'logo_horizontal' => asset('images/logo-nexus-acad-horizontal.png'),
            'logo_icone' => asset('images/icon-nexus-acad.png'),
            'cor_primaria' => '#667EEA',
            'cor_secundaria' => '#764BA2',
            'cor_acento' => '#F59E0B',
            'cor_texto' => '#1F2937',
            'fonte_principal' => 'Inter',
            'fonte_secundaria' => 'Poppins',
            'texto_rodape' => '© ' . date('Y') . ' Nexus.ACAD - Sistema de Gestão Acadêmica',
            'usar_logo_documentos' => true,
            'usar_marca_dagua' => false,
            'posicao_logo' => 'topo-esquerda',
        ];
    }

    /**
     * Verifica se entidade tem identidade própria
     */
    public function hasIdentidadeVisualPropria(): bool
    {
        return $this->identidadeVisual()->exists();
    }

    /**
     * Descobre de onde vem a identidade (própria ou herdada)
     */
    public function getOrigemIdentidadeVisual(): array
    {
        if ($this->hasIdentidadeVisualPropria()) {
            return [
                'origem' => 'propria',
                'entidade' => $this->getMorphClass(),
                'nome' => $this->nome ?? $this->razao_social ?? 'Entidade',
            ];
        }
 
        if ($this instanceof \App\Models\Instituicao && $this->mantenedora) {
            return $this->mantenedora->getOrigemIdentidadeVisual();
        }

        if ($this instanceof \App\Models\Mantenedora && $this->grupoEducacional) {
            if ($this->grupoEducacional->hasIdentidadeVisualPropria()) {
                return [
                    'origem' => 'herdada',
                    'entidade' => 'Grupo Educacional',
                    'nome' => $this->grupoEducacional->nome,
                ];
            }
        }

        return [
            'origem' => 'padrao',
            'entidade' => 'Sistema',
            'nome' => 'Nexus.ACAD',
        ];
    }
}
