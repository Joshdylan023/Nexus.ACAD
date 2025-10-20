<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRSyncLog extends Model
{
    use HasFactory;

    protected $table = 'hr_sync_logs';

    protected $fillable = [
        'hr_integration_id',
        'type',
        'status',
        'records_total',
        'records_created',
        'records_updated',
        'records_failed',
        'records_skipped',
        'started_at',
        'completed_at',
        'duration_seconds',
        'message',
        'errors',
        'summary',
        'triggered_by',
        'trigger_type'
    ];

    protected $casts = [
        'errors' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer' // ✅ ADICIONADO: Força conversão para integer
    ];

    /**
     * Relacionamento com integração
     */
    public function integration()
    {
        return $this->belongsTo(HRIntegration::class, 'hr_integration_id');
    }

    /**
     * Relacionamento com usuário que disparou
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * Relacionamento com erros detalhados
     */
    public function syncErrors()
    {
        return $this->hasMany(HRSyncError::class, 'sync_log_id');
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Iniciar sincronização
     */
    public function start(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now()
        ]);
    }

    /**
     * Completar sincronização com sucesso
     */
    public function complete(array $summary = []): void
    {
        // ✅ CORRIGIDO: Garantir que seja inteiro e positivo
        $duration = $this->started_at 
            ? (int) max(0, now()->diffInSeconds($this->started_at))
            : 0;

        // ✅ CORRIGIDO: Atualizar contadores individuais a partir do sumário
        $this->update(array_merge(
            [
                'status' => 'completed',
                'completed_at' => now(),
                'duration_seconds' => $duration,
                'summary' => $summary
            ],
            [
                'records_created' => $summary['records_created'] ?? 0,
                'records_updated' => $summary['records_updated'] ?? 0,
                'records_failed' => $summary['records_failed'] ?? 0,
                'records_skipped' => $summary['records_skipped'] ?? 0,
            ]
        ));
    }

    /**
     * Marcar sincronização como falha
     */
    public function fail(string $message, array $errors = []): void
    {
        // ✅ CORRIGIDO: Garantir que seja inteiro e positivo
        $duration = $this->started_at 
            ? (int) max(0, now()->diffInSeconds($this->started_at))
            : 0;
        
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => $duration,
            'message' => $message,
            'errors' => $errors
        ]);
    }

    /**
     * Incrementar contador de registros
     */
    public function incrementRecords(string $type, int $count = 1): void
    {
        $field = "records_{$type}";
        if (in_array($field, $this->fillable)) {
            $this->increment($field, $count);
        }
    }

    /**
     * Taxa de sucesso
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->records_total === 0) return 0;
        
        $successful = $this->records_created + $this->records_updated;
        return round(($successful / $this->records_total) * 100, 2);
    }

    /**
     * Formatar duração
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) return '0s';
        
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        
        return $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
    }

    /**
     * Verificar se tem erros
     */
    public function hasErrors(): bool
    {
        return $this->records_failed > 0 || !empty($this->errors);
    }
}
