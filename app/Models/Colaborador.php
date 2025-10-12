<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Colaborador extends Authenticatable
{
    use HasFactory;
    
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
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * A "Pessoa" (identidade única) por trás do vínculo de colaborador.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A "empresa" a que o colaborador pertence (Grupo, Mantenedora, etc.).
     */
    public function unidadeOrganizacional()
    {
        return $this->morphTo('unidade_organizacional');
    }

    /**
     * O local físico/lógico onde o colaborador trabalha.
     */
    public function unidadeLotacao()
    {
        return $this->morphTo('unidade_lotacao');
    }
    
    /**
     * O setor específico (dentro da unidade de lotação) a que o colaborador pertence.
     */
    public function setor(): BelongsTo
    {
        return $this->belongsTo(SetorVinculo::class, 'setor_vinculo_id');
    }

    /**
     * O gestor imediato deste colaborador.
     */
    public function gestorImediato(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'gestor_imediato_id');
    }

    /**
     * A equipa que este colaborador (se for gestor) lidera.
     */
    public function equipe(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'gestor_imediato_id');
    }

    /**
     * As instituições a que este colaborador tem acesso.
     */
    public function instituicoesAcesso(): BelongsToMany
    {
        return $this->belongsToMany(Instituicao::class, 'colaborador_instituicao_acesso');
    }
}