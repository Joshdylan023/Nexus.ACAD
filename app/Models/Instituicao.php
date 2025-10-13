<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instituicao extends Model
{
    use HasFactory;

    protected $table = 'instituicoes';

    protected $fillable = [
        'razao_social',
        'cnpj',
        'nome_fantasia',
        'sigla',
        'tipo_organizacao_academica',
        'categoria_administrativa',
        'codigo_mec',
        'endereco_sede',
        'status',
        'mantenedora_id',
        'reitor_id',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep'
    ];

    // Relacionamentos
    public function mantenedora()
    {
        return $this->belongsTo(Mantenedora::class);
    }

    public function reitor()
    {
        return $this->belongsTo(User::class, 'reitor_id');
    }

    public function campi()
    {
        return $this->hasMany(Campus::class);
    }

    public function atosRegulatorios()
    {
        return $this->hasMany(InstituicaoAtoRegulatorio::class);
    }

    public function setores()
    {
        return $this->morphToMany(Setor::class, 'setorable');
    }
    public function setorVinculos()
    {
        return $this->morphMany(SetorVinculo::class, 'vinculavel');
    }
}
