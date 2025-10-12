<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Perfil extends SpatieRole
{
    use HasFactory;

    protected $table = 'perfis';

    protected $fillable = ['name', 'guard_name', 'descricao'];


}
