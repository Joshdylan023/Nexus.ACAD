<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservaEspaco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reservas_espacos';

    protected $fillable = [
        'espaco_fisico_id',
        'solicitante_id',
        'aprovado_por',
        'data_inicio',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'recorrente',
        'dias_semana',
        'motivo',
        'descricao',
        'quantidade_pessoas',
        'recursos_adicionais',
        'status',
        'observacoes',
        'motivo_rejeicao',
        'finalidade',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
        'recorrente' => 'boolean',
        'dias_semana' => 'array',
        'recursos_adicionais' => 'array',
    ];

    // Relacionamentos
    public function espacoFisico()
    {
        return $this->belongsTo(EspacoFisico::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
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
    public function scopePendente($query)
    {
        return $query->where('status', 'Pendente');
    }

    public function scopeAprovada($query)
    {
        return $query->where('status', 'Aprovada');
    }
}
