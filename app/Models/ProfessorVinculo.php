<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorVinculo extends Model
{
    use HasFactory;

    protected $table = 'professor_vinculos';

    protected $fillable = [
        'user_id',
        'instituicao_id',
        'matricula_funcional',
        'status',
        'regime_contratacao',
        'tipo_contrato',
        'carga_horaria_contratual',
        'nivel_carreira',
        'biografia',
        'data_contratacao', // Adicionado
        'data_afastamento', // Adicionado
        'data_desligamento', // Adicionado
    ];

    // Converte as strings de data para objetos Carbon automaticamente
    protected $casts = [
        'data_contratacao' => 'date',
        'data_afastamento' => 'date',
        'data_desligamento' => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }
}
