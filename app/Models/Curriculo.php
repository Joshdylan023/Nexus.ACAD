<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curriculo extends Model
{
    use HasFactory;

    protected $table = 'curriculos';

    // ATUALIZE ESTA LINHA
    protected $fillable = [
        'curso_id', 'nome_matriz', 'tipo_matriz', 'codigo_curriculo',
        'data_inicio_vigencia', 'data_fim_vigencia', 'observacoes',
        'total_horas_aac_obrigatorias', 'carga_horaria_estagio_obrigatorio',
        'requer_aprovacao_npj',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function disciplinas(): BelongsToMany
    {
        return $this->belongsToMany(Disciplina::class, 'curriculo_disciplina')
            ->withPivot('periodo_sugerido', 'tipo_disciplina', 'pre_requisitos')
            ->withTimestamps();
    }
}
