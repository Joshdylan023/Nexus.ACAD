<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Campus extends Model
{
    use HasFactory;

    protected $table = 'campi';
    
    protected $fillable = [
        'instituicao_id',
        'nome',
        'endereco_completo',
        'gerente_unidade_id',
        'status',
    ];

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }
    
    public function gerenteUnidade(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerente_unidade_id');
    }

    public function setores(): MorphToMany
    {
        return $this->morphToMany(Setor::class, 'vinculavel', 'setor_vinculos')
                    ->withPivot('id', 'gestor_id', 'status', 'centro_custo_sap', 'centro_resultado_sap', 'requer_portaria_nomeacao_gestor', 'pai_id')
                    ->withTimestamps();
    }
}
