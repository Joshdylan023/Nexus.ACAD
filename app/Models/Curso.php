<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'instituicao_id',
        'area_conhecimento_id',
        'nome',
        'codigo_interno',
        'nivel',
        'duracao_padrao_semestres',
        'prazo_maximo_semestres',
        'coordenador_id',
        'status',
        'vagas_anuais',
    ];

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }

    public function areaConhecimento(): BelongsTo
    {
        return $this->belongsTo(AreaConhecimento::class);
    }

    public function coordenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordenador_id');
    }

    public function atosRegulatorios(): HasMany
    {
        return $this->hasMany(CursosAtoRegulatorio::class);
    }
}
