<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Auditable;

class Setor extends Model
{
    use HasFactory, Auditable; // ← ADICIONE O TRAIT AUDITABLE

    protected $table = 'setores';

    protected $fillable = ['nome', 'sigla', 'tipo'];

    // ← ADICIONE: Carregar relacionamentos de auditoria automaticamente
    protected $with = ['creator', 'updater'];

    public function pai(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_pai_id');
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(SetorVinculo::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function gruposEducacionais(): MorphedByMany
    {
        return $this->morphedByMany(GrupoEducacional::class, 'vinculavel', 'setor_vinculos');
    }

    public function campi(): MorphedByMany
    {
        return $this->morphedByMany(Campus::class, 'vinculavel', 'setor_vinculos');
    }
}
