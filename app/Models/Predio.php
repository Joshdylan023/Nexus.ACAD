<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Predio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'predios';

    protected $fillable = [
        'campus_id',
        'codigo',
        'nome',
        'descricao',
        'endereco',
        'latitude',
        'longitude',
        'total_andares',
        'total_blocos',
        'ano_construcao',
        'area_construida',
        'acessibilidade',
        'elevador',
        'ar_condicionado',
        'wifi',
        'status',
        'fotos',
        'documentos',
        'observacoes',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'area_construida' => 'decimal:2',
        'acessibilidade' => 'boolean',
        'elevador' => 'boolean',
        'ar_condicionado' => 'boolean',
        'wifi' => 'boolean',
        'fotos' => 'array',
        'documentos' => 'array',
    ];

    // Relacionamentos
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function blocos()
    {
        return $this->hasMany(Bloco::class);
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

    public function scopeComAcessibilidade($query)
    {
        return $query->where('acessibilidade', true);
    }

    // Acessores
    public function getTotalEspacosAttribute()
    {
        return EspacoFisico::whereHas('andar.bloco', function($q) {
            $q->where('predio_id', $this->id);
        })->count();
    }
}
