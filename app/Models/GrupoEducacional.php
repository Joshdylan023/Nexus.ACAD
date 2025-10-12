<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SetorVinculo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class GrupoEducacional extends Model
{
    use HasFactory;

    protected $table = 'grupos_educacionais';

    protected $fillable = [
        'nome',
        'cnpj',
        'endereco_completo',
        'representante_legal',
    ];

    public function mantenedoras(): HasMany
    {
        return $this->hasMany(Mantenedora::class);
    }

    public function setorVinculos(): MorphMany
    {
        return $this->morphMany(SetorVinculo::class, 'vinculavel');
    }

    /**
     * Define a relação polimórfica muitos-para-muitos com Setor através da tabela setor_vinculos.
     */
    public function setores(): MorphToMany
    {
        return $this->morphToMany(Setor::class, 'vinculavel', 'setor_vinculos')
                    ->withPivot('id', 'gestor_id', 'status', 'centro_custo_sap', 'centro_resultado_sap', 'requer_portaria_nomeacao_gestor')
                    ->withTimestamps();
    }
}