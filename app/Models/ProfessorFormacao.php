<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorFormacao extends Model
{
    use HasFactory;

    protected $table = 'professor_formacao';

    protected $fillable = ['professor_id', 'nivel', 'curso', 'instituicao', 'ano_conclusao'];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(ProfessorVinculo::class, 'professor_id');
    }
}
