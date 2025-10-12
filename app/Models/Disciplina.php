<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disciplina extends Model
{
    use HasFactory;

    protected $table = 'disciplinas';

    protected $fillable = ['nome', 'codigo', 'carga_horaria_total'];

    // Define a relação de muitos-para-muitos com Currículos
    public function curriculos(): BelongsToMany
    {
        return $this->belongsToMany(Curriculo::class, 'curriculo_disciplina')
            ->withPivot('periodo_sugerido', 'tipo_disciplina', 'pre_requisitos')
            ->withTimestamps();
    }
}
