<?php

namespace App\Observers;

use App\Models\Curso;
use App\Models\CoordenadorCurso;
use Illuminate\Support\Facades\Log;

class CursoObserver
{
    /**
     * Handle the Curso "updated" event.
     */
    public function updated(Curso $curso): void
    {
        // ✅ Verificar se o coordenador foi alterado
        if ($curso->isDirty('coordenador_id')) {
            $this->sincronizarCoordenador($curso);
        }
    }

    /**
     * ✅ Sincronizar coordenador do curso com a tabela coordenadores_curso
     */
    private function sincronizarCoordenador(Curso $curso): void
    {
        try {
            // ✅ CASO 1: Removeu o coordenador do curso
            if (!$curso->coordenador_id) {
                $this->removerCoordenadorTitular($curso);
                return;
            }

            // ✅ CASO 2: Verificar se já existe registro ativo para este coordenador
            $coordenadorExistente = CoordenadorCurso::where('curso_id', $curso->id)
                ->where('colaborador_id', $curso->coordenador_id)
                ->where('tipo', 'Titular')
                ->where('status', 'Ativo')
                ->where(function($query) {
                    $query->whereNull('data_fim')
                          ->orWhere('data_fim', '>=', now());
                })
                ->first();

            if ($coordenadorExistente) {
                // Já existe e está ativo, não precisa fazer nada
                Log::info('Coordenador já está ativo no curso', [
                    'curso_id' => $curso->id,
                    'coordenador_id' => $curso->coordenador_id
                ]);
                return;
            }

            // ✅ CASO 3: Criar novo coordenador e inativar o anterior
            $this->substituirCoordenadorTitular($curso);

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar coordenador do curso', [
                'curso_id' => $curso->id,
                'coordenador_id' => $curso->coordenador_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ Remover coordenador titular do curso
     */
    private function removerCoordenadorTitular(Curso $curso): void
    {
        $inativados = CoordenadorCurso::where('curso_id', $curso->id)
            ->where('tipo', 'Titular')
            ->where('status', 'Ativo')
            ->update([
                'status' => 'Inativo',
                'data_fim' => now(),
                'observacoes' => 'Coordenador removido do curso em ' . now()->format('d/m/Y H:i'),
                'updated_by' => auth()->id()
            ]);

        if ($inativados > 0) {
            Log::info('Coordenador titular inativado', [
                'curso_id' => $curso->id,
                'inativados' => $inativados
            ]);
        }
    }

    /**
     * ✅ Substituir coordenador titular
     */
    private function substituirCoordenadorTitular(Curso $curso): void
    {
        // Inativar o coordenador titular anterior
        $coordenadorAnterior = CoordenadorCurso::where('curso_id', $curso->id)
            ->where('tipo', 'Titular')
            ->where('status', 'Ativo')
            ->first();

        if ($coordenadorAnterior) {
            $coordenadorAnterior->update([
                'status' => 'Inativo',
                'data_fim' => now(),
                'observacoes' => ($coordenadorAnterior->observacoes ? $coordenadorAnterior->observacoes . ' | ' : '') . 
                                'Substituído em ' . now()->format('d/m/Y H:i'),
                'updated_by' => auth()->id()
            ]);

            Log::info('Coordenador anterior inativado', [
                'curso_id' => $curso->id,
                'coordenador_anterior_id' => $coordenadorAnterior->id
            ]);
        }

        // ✅ Criar novo registro de coordenador titular
        $novoCoordenador = CoordenadorCurso::create([
            'curso_id' => $curso->id,
            'colaborador_id' => $curso->coordenador_id,
            'tipo' => 'Titular',
            'status' => 'Ativo',
            'data_inicio' => now(),
            'data_fim' => null,
            'portaria' => 'Auto-gerado via atualização de curso',
            'observacoes' => 'Criado automaticamente ao atualizar o curso em ' . now()->format('d/m/Y H:i'),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        Log::info('Novo coordenador titular criado', [
            'curso_id' => $curso->id,
            'coordenador_id' => $novoCoordenador->id,
            'colaborador_id' => $curso->coordenador_id
        ]);
    }
}
