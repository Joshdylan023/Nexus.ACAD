<?php

namespace App\Observers;

use App\Models\CoordenadorCurso;
use App\Models\Curso;
use Illuminate\Support\Facades\Log;

class CoordenadorCursoObserver
{
    /**
     * Handle the CoordenadorCurso "created" event.
     */
    public function created(CoordenadorCurso $coordenador): void
    {
        $this->sincronizarComCurso($coordenador);
    }

    /**
     * Handle the CoordenadorCurso "updated" event.
     */
    public function updated(CoordenadorCurso $coordenador): void
    {
        $this->sincronizarComCurso($coordenador);
    }

    /**
     * Handle the CoordenadorCurso "deleted" event.
     */
    public function deleted(CoordenadorCurso $coordenador): void
    {
        // Se era titular ativo, remover do curso
        if ($coordenador->tipo === 'Titular' && $coordenador->status === 'Ativo') {
            Curso::where('id', $coordenador->curso_id)
                ->where('coordenador_id', $coordenador->colaborador_id)
                ->update(['coordenador_id' => null]);

            Log::info('Coordenador removido do curso após exclusão', [
                'curso_id' => $coordenador->curso_id,
                'coordenador_id' => $coordenador->id
            ]);
        }
    }

    /**
     * ✅ Sincronizar coordenador titular com o curso
     */
    private function sincronizarComCurso(CoordenadorCurso $coordenador): void
    {
        try {
            // ✅ Só sincroniza se for Titular Ativo e sem data de fim (ou data fim futura)
            $isAtivo = $coordenador->tipo === 'Titular' 
                && $coordenador->status === 'Ativo'
                && (!$coordenador->data_fim || $coordenador->data_fim >= now());

            if ($isAtivo) {
                $curso = Curso::find($coordenador->curso_id);
                
                if ($curso) {
                    // Atualizar apenas se for diferente (evitar loop infinito)
                    if ($curso->coordenador_id !== $coordenador->colaborador_id) {
                        $curso->coordenador_id = $coordenador->colaborador_id;
                        $curso->saveQuietly(); // ✅ saveQuietly evita disparar o observer novamente
                        
                        Log::info('Curso atualizado com novo coordenador', [
                            'curso_id' => $curso->id,
                            'colaborador_id' => $coordenador->colaborador_id
                        ]);
                    }
                }
            } 
            // ✅ Se mudou para inativo ou tem data fim passada, remover do curso
            elseif ($coordenador->tipo === 'Titular' && 
                    ($coordenador->status === 'Inativo' || 
                     ($coordenador->data_fim && $coordenador->data_fim < now()))) {
                
                $curso = Curso::find($coordenador->curso_id);
                
                if ($curso && $curso->coordenador_id === $coordenador->colaborador_id) {
                    $curso->coordenador_id = null;
                    $curso->saveQuietly();
                    
                    Log::info('Coordenador removido do curso por inativação', [
                        'curso_id' => $curso->id,
                        'coordenador_id' => $coordenador->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar curso com coordenador', [
                'coordenador_id' => $coordenador->id,
                'curso_id' => $coordenador->curso_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
