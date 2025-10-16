<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsActivity; // ← NOVO TRAIT

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, LogsActivity; // ← ADICIONADO LogsActivity

    protected $fillable = [
        'name',
        'nome_social',
        'email',
        'email_pessoal',
        'password',
        'cpf',
        'rg',
        'rg_orgao_expedidor',
        'rg_data_expedicao',
        'data_nascimento',
        'telefone_principal',
        'telefone_secundario',
        'nacionalidade',
        'naturalidade_cidade',
        'naturalidade_uf',
        'endereco_completo',
        'nome_pai',
        'nome_mae',
        'ensino_medio_instituicao',
        'ensino_medio_ano_conclusao',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'data_nascimento' => 'date',
        'rg_data_expedicao' => 'date',
    ];

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
        return 'Usuário';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->name;
    }

    /**
     * Não logar alterações sensíveis
     */
    protected function shouldLogUpdate(): bool
    {
        $ignoredFields = [
            'updated_at',
            'remember_token',
            'password',
            'email_verified_at',
            'last_login_at', // se tiver
        ];
        
        $dirtyFields = array_keys($this->getDirty());
        $relevantChanges = array_diff($dirtyFields, $ignoredFields);
        
        return count($relevantChanges) > 0;
    }

    // =========================================
    // RELACIONAMENTOS
    // =========================================

    public function colaborador()
    {
        return $this->hasOne(Colaborador::class, 'user_id');
    }

    public function perfis()
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
