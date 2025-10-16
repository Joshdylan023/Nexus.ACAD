<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Traits\Auditable;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class Campus extends Model
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO LogsActivity

    protected $table = 'campi';
    
    protected $fillable = [
        'instituicao_id',
        'nome',
        'endereco_completo',
        'gerente_unidade_id',
        'status',
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
        return 'Campus';
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
