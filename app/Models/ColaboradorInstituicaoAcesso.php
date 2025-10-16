<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColaboradorInstituicaoAcesso extends Pivot
{
    protected $table = 'colaborador_instituicao_acesso';

    protected $fillable = [
        'colaborador_id',
        'instituicao_id',
        'roles',
        'permissions',
    ];

    protected $casts = [
        'roles' => 'array',
        'permissions' => 'array',
    ];

    /**
     * Relacionamento com Colaborador
     */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    /**
     * Relacionamento com Instituição
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }
}
