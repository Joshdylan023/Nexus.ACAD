<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bloco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blocos';

    protected $fillable = [
        'predio_id',
        'codigo',
        'nome',
        'descricao',
        'total_andares',
        'acessibilidade',
        'status',
        'fotos',
        'observacoes',
        'updated_by',
    ];

    protected $casts = [
        'acessibilidade' => 'boolean',
        'fotos' => 'array',
    ];

    // Relacionamentos
    public function predio()
    {
        return $this->belongsTo(Predio::class);
    }

    public function andares()
    {
        return $this->hasMany(Andar::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('status', 'Ativo');
    }

    // Acessores
    public function getTotalEspacosAttribute()
    {
        return EspacoFisico::whereHas('andar', function($q) {
            $q->where('bloco_id', $this->id);
        })->count();
    }
}
