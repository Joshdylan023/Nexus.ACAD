<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursosAtoRegulatorio extends Model
{
    use HasFactory;

    protected $table = 'cursos_atos_regulatorios';

    protected $fillable = [
        'curso_id',
        'codigo_mec',
        'codigo_emec',
        'tipo_ato',
        'numero_portaria',
        'data_publicacao_dou',
        'link_publicacao',
        'data_validade_ato',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
