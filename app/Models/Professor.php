<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professor extends Model
{
    use HasFactory;

    protected $table = 'professores';

    protected $fillable = [
        'user_id', 'matricula_funcional', 'regime_contratacao', 'tipo_contrato',
        'carga_horaria_contratual', 'nivel_carreira', 'biografia',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formacao(): HasMany
    {
        return $this->hasMany(ProfessorFormacao::class);
    }
}
