<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InstituicaoSetor extends Pivot
{
    protected $table = 'instituicao_setor';

    protected $with = ['gestor'];

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }
}
