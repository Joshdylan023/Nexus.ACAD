<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\Auditable;
use App\Traits\HasIdentidadeVisual;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class Mantenedora extends Model
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO LogsActivity
    use HasIdentidadeVisual;

    protected $table = 'mantenedoras';

    protected $fillable = [
        'grupo_educacional_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco_completo',
        'representante_legal',
        'created_by', // ← Certifique-se de ter esses campos
        'updated_by',
    ];

    protected $appends = ['nome'];

    // Relacionamentos de auditoria (do Auditable)
    protected $with = ['creator', 'updater'];

    // =========================================
    // ACCESSOR
    // =========================================
    
    public function getNomeAttribute()
    {
        return $this->nome_fantasia ?? $this->razao_social;
    }

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
        return 'Mantenedora';
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

    public function grupoEducacional(): BelongsTo
    {
        return $this->belongsTo(GrupoEducacional::class);
    }

    public function instituicoes(): HasMany
    {
        return $this->hasMany(Instituicao::class);
    }

    /**
     * Uma Mantenedora pode ter muitos vínculos de setor.
     */
    public function vinculosDeSetor(): MorphMany
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
