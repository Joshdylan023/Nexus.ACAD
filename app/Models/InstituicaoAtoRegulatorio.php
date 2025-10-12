<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituicaoAtoRegulatorio extends Model
{
    use HasFactory;

    protected $table = 'instituicao_atos_regulatorios';

    protected $fillable = [
        'instituicao_id',
        'tipo_ato',
        'numero_portaria',
        'data_publicacao_dou',
        'link_publicacao',
        'data_validade_ato',
    ];

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }
}
