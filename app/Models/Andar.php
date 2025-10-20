<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Andar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'andares';

    protected $fillable = [
        'bloco_id',
        'numero',
        'nome',
        'descricao',
        'area_util',
        'acessibilidade',
        'status',
        'fotos',
        'observacoes',
        'updated_by',
    ];

    protected $casts = [
        'area_util' => 'decimal:2',
        'acessibilidade' => 'boolean',
        'fotos' => 'array',
    ];

    // Relacionamentos
    public function bloco()
    {
        return $this->belongsTo(Bloco::class);
    }

    public function espacosFisicos()
    {
        return $this->hasMany(EspacoFisico::class);
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
    public function getNomeCompletoAttribute()
    {
        if ($this->numero == 0) {
            return 'Térreo';
        } elseif ($this->numero < 0) {
            return abs($this->numero) . 'º Subsolo';
        } else {
            return $this->numero . 'º Andar';
        }
    }
}
