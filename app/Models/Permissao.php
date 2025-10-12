<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permissao extends SpatiePermission
{
    use HasFactory;

    protected $table = 'permissoes';

    protected $fillable = ['name', 'guard_name', 'descricao'];

    public function perfis(): BelongsToMany
    {
        return $this->belongsToMany(Perfil::class, 'perfil_permissao');
    }
}
