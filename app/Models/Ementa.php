<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ementa extends Model
{
    use HasFactory;

    protected $table = 'ementas';

    protected $fillable = ['disciplina_id', 'titulo', 'versao', 'ementa_resumida', 'conteudo_detalhado', 'bibliografia', 'data_inicio_vigencia', 'data_fim_vigencia'];

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }
}
