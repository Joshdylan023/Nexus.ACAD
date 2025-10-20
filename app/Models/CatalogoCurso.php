<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoCurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalogo_cursos';

    protected $fillable = [
        'grupo_educacional_id',
        'area_conhecimento_id',
        'codigo',
        'nome',
        'sigla',
        'nivel',
        'grau',
        'modalidade',
        'duracao_padrao_semestres',
        'prazo_maximo_semestres',
        'carga_horaria_total',
        'descricao',
        'objetivo',
        'perfil_egresso',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'duracao_padrao_semestres' => 'integer',
        'prazo_maximo_semestres' => 'integer',
        'carga_horaria_total' => 'integer'
    ];

    protected $appends = ['nome_completo'];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================

    public function grupoEducacional(): BelongsTo
    {
        return $this->belongsTo(GrupoEducacional::class);
    }

    public function areaConhecimento(): BelongsTo
    {
        return $this->belongsTo(AreaConhecimento::class);
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'catalogo_curso_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_educacional_id', $grupoId);
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopePorModalidade($query, $modalidade)
    {
        return $query->where('modalidade', $modalidade);
    }

    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('codigo', 'like', "%{$termo}%")
              ->orWhere('nome', 'like', "%{$termo}%")
              ->orWhere('sigla', 'like', "%{$termo}%");
        });
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getNomeCompletoAttribute(): string
    {
        return "{$this->codigo} - {$this->nome}";
    }

    // ==========================================
    // MUTATORS
    // ==========================================

    public function setCodigoAttribute($value)
    {
        $this->attributes['codigo'] = strtoupper($value);
    }

    public function setSiglaAttribute($value)
    {
        $this->attributes['sigla'] = strtoupper($value);
    }
}
