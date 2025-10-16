<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Traits\Auditable;
use App\Traits\HasIdentidadeVisual;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class GrupoEducacional extends Model
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO LogsActivity
    use HasIdentidadeVisual;

    protected $table = 'grupos_educacionais';

    protected $fillable = [
        'nome',
        'razao_social',
        'cnpj',
        'endereco_completo',
        'representante_legal',
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
        return 'Grupo Educacional';
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
