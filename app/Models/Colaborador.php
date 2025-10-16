<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Auditable;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class Colaborador extends Authenticatable
{
    use HasFactory, Auditable, LogsActivity; // ← ADICIONADO Auditable e LogsActivity
    
    protected $table = 'colaboradores';
    
    protected $fillable = [
        'user_id',
        'unidade_organizacional_id',
        'unidade_organizacional_type',
        'unidade_lotacao_id',
        'unidade_lotacao_type',
        'setor_vinculo_id',
        'gestor_imediato_id',
        'is_gestor',
        'matricula_funcional',
        'email_funcional',
        'password',
        'cargo',
        'data_admissao',
        'data_desligamento',
        'status',
        'foto_registro_rh',
        'created_by', // ← Certifique-se de ter esses campos
        'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'setor_nome',
        'unidade_lotacao_nome',
        'unidade_organizacional_nome'
    ];

    // Relacionamentos de auditoria (do Auditable)
    protected $with = ['creator', 'updater'];

    // =========================================
    // ACCESSORS
    // =========================================

    public function getSetorNomeAttribute()
    {
        return $this->setorVinculo?->setor?->nome ?? 'Não informado';
    }

    public function getUnidadeLotacaoNomeAttribute()
    {
        if (!$this->unidadeLotacao) {
            return 'Não informado';
        }

        return $this->unidadeLotacao->nome 
            ?? $this->unidadeLotacao->nome_fantasia 
            ?? $this->unidadeLotacao->razao_social 
            ?? $this->unidadeLotacao->sigla
            ?? 'Não informado';
    }

    public function getUnidadeOrganizacionalNomeAttribute()
    {
        if (!$this->unidadeOrganizacional) {
            return 'Não informado';
        }

        return $this->unidadeOrganizacional->nome 
            ?? $this->unidadeOrganizacional->nome_fantasia 
            ?? $this->unidadeOrganizacional->razao_social 
            ?? $this->unidadeOrganizacional->sigla
            ?? 'Não informado';
    }

    // =========================================
    // CONFIGURAÇÃO DE LOGS DE AUDITORIA
    // =========================================
    
    /**
     * Define o módulo para logs de auditoria
     */
    protected function getLogModule(): string
    {
        return 'pessoas_acessos';
    }

    /**
     * Nome amigável do modelo para logs
     */
    protected function getModelFriendlyName(): string
    {
        return 'Colaborador';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->usuario?->name ?? "Matrícula {$this->matricula_funcional}";
    }

    /**
     * Não logar alterações de senha
     */
    protected function shouldLogUpdate(): bool
    {
        $ignoredFields = [
            'updated_at',
            'updated_by',
            'password',
        ];
        
        $dirtyFields = array_keys($this->getDirty());
        $relevantChanges = array_diff($dirtyFields, $ignoredFields);
        
        return count($relevantChanges) > 0;
    }

    // =========================================
    // RELACIONAMENTOS
    // =========================================

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unidadeOrganizacional(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'unidade_organizacional_type', 'unidade_organizacional_id');
    }

    public function unidadeLotacao(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'unidade_lotacao_type', 'unidade_lotacao_id');
    }
    
    public function setorVinculo(): BelongsTo
    {
        return $this->belongsTo(SetorVinculo::class, 'setor_vinculo_id');
    }

    public function gestorImediato(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'gestor_imediato_id');
    }

    public function equipe(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'gestor_imediato_id');
    }

    public function instituicoesAcesso(): BelongsToMany
    {
        return $this->belongsToMany(
            Instituicao::class,
            'colaborador_instituicao_acesso',
            'colaborador_id',
            'instituicao_id'
        )
        ->using(ColaboradorInstituicaoAcesso::class)
        ->withPivot(['roles', 'permissions'])
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

    // =========================================
    // MÉTODOS DE PERMISSÕES POR INSTITUIÇÃO
    // =========================================

    public function getRolesForInstituicao($instituicaoId): array
    {
        $acesso = $this->instituicoesAcesso()
            ->where('instituicao_id', $instituicaoId)
            ->first();

        if (!$acesso || !$acesso->pivot->roles) {
            return [];
        }

        if (is_array($acesso->pivot->roles)) {
            return $acesso->pivot->roles;
        }

        if (is_string($acesso->pivot->roles)) {
            $decoded = json_decode($acesso->pivot->roles, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function getPermissionsForInstituicao($instituicaoId): array
    {
        $acesso = $this->instituicoesAcesso()
            ->where('instituicao_id', $instituicaoId)
            ->first();

        if (!$acesso || !$acesso->pivot->permissions) {
            return [];
        }

        if (is_array($acesso->pivot->permissions)) {
            return $acesso->pivot->permissions;
        }

        if (is_string($acesso->pivot->permissions)) {
            $decoded = json_decode($acesso->pivot->permissions, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function syncRolesForInstituicao($instituicaoId, array $roles): void
    {
        $this->instituicoesAcesso()->updateExistingPivot($instituicaoId, [
            'roles' => $roles
        ]);
    }

    public function syncPermissionsForInstituicao($instituicaoId, array $permissions): void
    {
        $this->instituicoesAcesso()->updateExistingPivot($instituicaoId, [
            'permissions' => $permissions
        ]);
    }

    public function hasPermissionInInstituicao($permission, $instituicaoId): bool
    {
        $permissions = $this->getPermissionsForInstituicao($instituicaoId);
        return in_array($permission, $permissions);
    }

    public function hasRoleInInstituicao($role, $instituicaoId): bool
    {
        $roles = $this->getRolesForInstituicao($instituicaoId);
        return in_array($role, $roles);
    }
}
