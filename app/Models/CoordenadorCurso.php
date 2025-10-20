<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoordenadorCurso extends Model
{
    use HasFactory;

    protected $table = 'coordenadores_curso';

    protected $fillable = [
        'curso_id',
        'colaborador_id',
        'tipo',
        'data_inicio',
        'data_fim',
        'portaria',
        'status',
        'observacoes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    // ✅ RELACIONAMENTOS
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ✅ SCOPES
    public function scopeAtivos($query)
    {
        return $query->where('status', 'Ativo')
                     ->where(function($q) {
                         $q->whereNull('data_fim')
                           ->orWhere('data_fim', '>=', now());
                     });
    }

    public function scopeTitulares($query)
    {
        return $query->where('tipo', 'Titular');
    }

    public function scopeTitularesAtivos($query)
    {
        return $query->titulares()->ativos();
    }

    public function scopeAdjuntos($query)
    {
        return $query->where('tipo', 'Adjunto');
    }

    public function scopePorCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    // ✅ MÉTODOS AUXILIARES
    public function isAtivo()
    {
        return $this->status === 'Ativo' && 
               (is_null($this->data_fim) || $this->data_fim >= now());
    }

    public function isTitular()
    {
        return $this->tipo === 'Titular';
    }
}
