<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoEducacionalSetor extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grupo_educacional_setor';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the gestor associated with the pivot.
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }
}