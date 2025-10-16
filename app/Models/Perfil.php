<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\Auditable;
use App\Traits\LogsActivity;

class Perfil extends SpatieRole
{
    use HasFactory, Auditable, LogsActivity;

    protected $table = 'perfis';

    protected $fillable = ['name', 'guard_name', 'descricao'];

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
        return 'Perfil de Acesso';
    }

    /**
     * Identificador do registro para logs
     */
    protected function getLogIdentifier(): string
    {
        return $this->name;
    }
}
