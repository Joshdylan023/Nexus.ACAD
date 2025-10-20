<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspacoFisico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'espacos_fisicos';

    protected $fillable = [
        'andar_id',
        'codigo',
        'nome',
        'tipo',
        'area',
        'capacidade',
        'capacidade_exame',
        'ar_condicionado',
        'projetor',
        'lousa_digital',
        'computadores',
        'quantidade_computadores',
        'wifi',
        'acessibilidade',
        'cameras_seguranca',
        'sistema_som',
        'quantidade_carteiras',
        'quantidade_cadeiras',
        'quantidade_mesas',
        'tipo_mobiliario',
        'status',
        'permite_reserva',
        'horarios_disponiveis',
        'responsavel_id',
        'fotos',
        'videos_360',
        'documentos',
        'equipamentos',
        'observacoes',
        'restricoes',
        'updated_by',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'ar_condicionado' => 'boolean',
        'projetor' => 'boolean',
        'lousa_digital' => 'boolean',
        'computadores' => 'boolean',
        'wifi' => 'boolean',
        'acessibilidade' => 'boolean',
        'cameras_seguranca' => 'boolean',
        'sistema_som' => 'boolean',
        'permite_reserva' => 'boolean',
        'horarios_disponiveis' => 'array',
        'fotos' => 'array',
        'videos_360' => 'array',
        'documentos' => 'array',
        'equipamentos' => 'array',
    ];

    // Relacionamentos
    public function andar()
    {
        return $this->belongsTo(Andar::class);
    }

    public function responsavel()
    {
        return $this->belongsTo(Colaborador::class, 'responsavel_id');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaEspaco::class);
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
    public function scopeDisponivel($query)
    {
        return $query->where('status', 'Disponível');
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeComCapacidadeMinima($query, $capacidade)
    {
        return $query->where('capacidade', '>=', $capacidade);
    }

    // Acessores
    public function getLocalizacaoCompletaAttribute()
    {
        $andar = $this->andar;
        $bloco = $andar->bloco;
        $predio = $bloco->predio;
        
        return "{$predio->nome} - {$bloco->nome} - {$andar->nome_completo} - {$this->codigo}";
    }
}
