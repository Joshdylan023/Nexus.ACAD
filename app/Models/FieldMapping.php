<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_integration_id',
        'entity_type',
        'source_field',
        'target_field',
        'transform_function',
        'is_required',
        'default_value',
        'validation_rules'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'validation_rules' => 'array'
    ];

    /**
     * Relacionamento com integração
     */
    public function integration()
    {
        return $this->belongsTo(HRIntegration::class, 'hr_integration_id');
    }

    /**
     * Scopes
     */
    public function scopeByEntityType($query, $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Aplicar transformação no valor
     */
    public function transform($value)
    {
        if (!$this->transform_function) {
            return $value;
        }

        // Executar função de transformação
        return match($this->transform_function) {
            'uppercase' => strtoupper($value),
            'lowercase' => strtolower($value),
            'trim' => trim($value),
            'date_format' => $this->formatDate($value),
            'cpf_format' => $this->formatCPF($value),
            default => $value
        };
    }

    private function formatDate($value)
    {
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function formatCPF($value)
    {
        return preg_replace('/\D/', '', $value);
    }
}
