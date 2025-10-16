<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use App\Traits\HasIdentidadeVisual;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class Instituicao extends Model
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO LogsActivity
    use HasIdentidadeVisual;

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
        'cep',
        'created_by', // ← Certifique-se de ter esses campos
        'updated_by',
    ];

    // Relacionamentos de auditoria (do Auditable)
    protected $with = ['creator', 'updater'];

    // =========================================
    // CONFIGURAÇÃO DE LOGS DE AUDITORIA
    // =========================================
    
    /**
     * Define o módulo para logs de auditoria
     */
    protected function getLogModule(): string
    {
        return 'institucional';
    }

    /**
     * Nome amigável do modelo para logs
     */
    protected function getModelFriendlyName(): string
    {
        return 'Instituição';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->nome_fantasia ?? $this->razao_social;
    }

    // =========================================
    // RELACIONAMENTOS
    // =========================================

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

    // Relacionamentos do Auditable (creator/updater)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
