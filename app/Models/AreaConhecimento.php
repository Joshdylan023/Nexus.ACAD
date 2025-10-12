<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaConhecimento extends Model
{
    use HasFactory;

    protected $table = 'areas_conhecimento';

    protected $fillable = ['grande_area_conhecimento_id', 'nome'];

    /**
     * Uma Área de Conhecimento PERTENCE A uma Grande Área.
     */
    public function grandeArea(): BelongsTo
    {
        return $this->belongsTo(GrandeAreaConhecimento::class, 'grande_area_conhecimento_id');
    }
}
