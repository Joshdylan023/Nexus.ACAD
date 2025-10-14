<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\Auditable;
use App\Traits\HasIdentidadeVisual;


class Mantenedora extends Model
{
    use HasFactory, Auditable;
    use HasIdentidadeVisual;// ← ADICIONE O TRAIT AUDITABLE

    protected $table = 'mantenedoras';

    protected $fillable = [
        'grupo_educacional_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco_completo',
        'representante_legal',
    ];

    protected $appends = ['nome'];

    // ← ADICIONE: Carregar relacionamentos de auditoria automaticamente
    protected $with = ['creator', 'updater'];

    public function getNomeAttribute()
    {
        return $this->nome_fantasia ?? $this->razao_social;
    }

    public function grupoEducacional(): BelongsTo
    {
        return $this->belongsTo(GrupoEducacional::class);
    }

    public function instituicoes(): HasMany
    {
        return $this->hasMany(Instituicao::class);
    }

    /**
     * Uma Mantenedora pode ter muitos vínculos de setor.
     */
    public function vinculosDeSetor(): MorphMany
    {
        return $this->morphMany(SetorVinculo::class, 'vinculavel');
    }
}
