<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable; // Removido duplicado

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

    // ✅ ADICIONE: Relação com notificações
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
