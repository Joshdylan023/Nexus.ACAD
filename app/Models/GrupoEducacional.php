<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Traits\Auditable;
use App\Traits\HasIdentidadeVisual;


class GrupoEducacional extends Model
{
    use HasFactory, Auditable; // ← ADICIONE O TRAIT AUDITABLE
    use HasIdentidadeVisual; // ← ADICIONE O TRAIT VISUAL


    protected $table = 'grupos_educacionais';

    protected $fillable = [
        'nome',
        'razao_social',
        'cnpj',
        'endereco_completo',
        'representante_legal',
    ];

    // ← ADICIONE: Carregar relacionamentos de auditoria automaticamente
    protected $with = ['creator', 'updater'];

    public function mantenedoras(): HasMany
    {
        return $this->hasMany(Mantenedora::class);
    }

    public function setores(): MorphToMany
    {
        return $this->morphToMany(Setor::class, 'vinculavel', 'setor_vinculos')
                    ->withPivot('id', 'gestor_id', 'status', 'centro_custo_sap', 'centro_resultado_sap', 'requer_portaria_nomeacao_gestor', 'pai_id')
                    ->withTimestamps();
    }
}
