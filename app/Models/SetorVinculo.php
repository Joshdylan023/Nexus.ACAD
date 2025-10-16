<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SetorVinculo extends Model
{
    // ✅ NOME CORRETO DA TABELA
    protected $table = 'setor_vinculos';
    
    protected $fillable = [
        'setor_id',
        'vinculavel_type',
        'vinculavel_id',
        'pai_id',
        'gestor_id',
        'status',
        'centro_custo_sap',
        'centro_resultado_sap',
        'requer_portaria_nomeacao_gestor',
    ];

    // ✅ RELACIONAMENTO COM SETOR REAL
    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    public function vinculavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'gestor_id');
    }

    public function pai(): BelongsTo
    {
        return $this->belongsTo(SetorVinculo::class, 'pai_id');
    }

    public function filhos()
    {
        return $this->hasMany(SetorVinculo::class, 'pai_id');
    }
}
