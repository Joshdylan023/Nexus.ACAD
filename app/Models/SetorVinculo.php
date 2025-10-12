<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SetorVinculo extends Model
{
    use HasFactory;

    protected $table = 'setor_vinculos';

    protected $fillable = [
        'setor_id',
        'vinculavel_id',
        'vinculavel_type',
        'pai_id',
        'gestor_id',
        'status',
        'centro_custo_sap',
        'centro_resultado_sap',
        'requer_portaria_nomeacao_gestor',
    ];

    /**
     * Relação polimórfica para obter o "dono" do vínculo (Campus, IES, etc.).
     */
    public function vinculavel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relação para obter o tipo de setor (do catálogo).
     */
    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    /**
     * Relação para obter o gestor (um User).
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Relação para obter o vínculo pai (outro SetorVinculo).
     */
    public function pai(): BelongsTo
    {
        return $this->belongsTo(SetorVinculo::class, 'pai_id');
    }
}