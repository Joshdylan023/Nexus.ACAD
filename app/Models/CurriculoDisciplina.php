<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculoDisciplina extends Model
{
    use HasFactory;

    protected $table = 'curriculo_disciplina';

    protected $fillable = [
        'curriculo_id',
        'disciplina_id',
        'periodo_sugerido',
        'tipo_disciplina',
        'pre_requisitos',
    ];

    public function curriculo()
    {
        return $this->belongsTo(Curriculo::class);
    }

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class);
    }
}
