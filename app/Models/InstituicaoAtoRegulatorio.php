<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class InstituicaoAtoRegulatorio extends Model
{
    use HasFactory, Auditable; // ← ADICIONE O TRAIT AUDITABLE

    protected $table = 'instituicao_atos_regulatorios';

    protected $fillable = [
        'instituicao_id',
        'tipo_ato',
        'numero_portaria',
        'data_publicacao_dou',
        'link_publicacao',
        'data_validade_ato',
    ];

    // ← ADICIONE: Carregar relacionamentos de auditoria automaticamente
    protected $with = ['creator', 'updater'];

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }
}
