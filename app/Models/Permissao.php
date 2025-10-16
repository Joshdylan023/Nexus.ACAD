<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\Auditable;
use App\Traits\LogsActivity;

class Permissao extends SpatiePermission
{
    use HasFactory, Auditable, LogsActivity;

    protected $table = 'permissoes';

    protected $fillable = ['name', 'guard_name', 'descricao'];

    /**
     * Relacionamento com perfis
     */
    public function perfis(): BelongsToMany
    {
        return $this->belongsToMany(Perfil::class, 'perfil_permissao');
    }

    /**
     * Módulo do sistema
     */
    protected function getLogModule(): string
    {
        return 'pessoas_acessos';
    }

    /**
     * Nome amigável do modelo
     */
    protected function getModelFriendlyName(): string
    {
        return 'Permissão';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->name;
    }
}
