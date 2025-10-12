<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Instituicao extends Model
{
    use HasFactory;

    protected $table = 'instituicoes';

    protected $fillable = [
        'mantenedora_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'tipo_organizacao_academica',
        'reitor_id',
        'endereco_sede',
        'status',
        'codigo_sap',
        'codigo_emec',
    ];

    protected $appends = ['nome'];

    public function getNomeAttribute()
    {
        return $this->nome_fantasia ?? $this->razao_social;
    }

    public function mantenedora(): BelongsTo
    {
        return $this->belongsTo(Mantenedora::class);
    }

    public function reitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reitor_id');
    }

    public function campi(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    /**
     * Retorna todos os vínculos de setor para esta instituição.
     */
    public function setorVinculos(): MorphMany
    {
        return $this->morphMany(SetorVinculo::class, 'vinculavel');
    }
}