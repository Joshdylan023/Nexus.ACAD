<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SetorVinculo extends Model
{
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

    protected $casts = [
        'requer_portaria_nomeacao_gestor' => 'boolean',
    ];

    public function vinculavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function pai(): BelongsTo
    {
        return $this->belongsTo(SetorVinculo::class, 'pai_id');
    }
}
