<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Auditable;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class Setor extends Model
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO LogsActivity

    protected $table = 'setores';

    protected $fillable = [
        'nome',
        'sigla',
        'tipo',
        'setor_pai_id', // ← Certifique-se de ter esses campos
        'gestor_id',
        'created_by',
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
        return 'Setor';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->nome;
    }

    // =========================================
    // RELACIONAMENTOS
    // =========================================

    public function pai(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_pai_id');
    }

    public function subsetores(): HasMany
    {
        return $this->hasMany(Setor::class, 'setor_pai_id');
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(SetorVinculo::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function gruposEducacionais(): MorphedByMany
    {
        return $this->morphedByMany(GrupoEducacional::class, 'vinculavel', 'setor_vinculos');
    }

    public function campi(): MorphedByMany
    {
        return $this->morphedByMany(Campus::class, 'vinculavel', 'setor_vinculos');
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
