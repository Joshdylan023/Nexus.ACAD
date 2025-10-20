<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRSyncError extends Model // ✅ RENOMEADO
{
    use HasFactory;

    protected $table = 'hr_sync_errors'; // ✅ ADICIONADO

    protected $fillable = [
        'sync_log_id',
        'entity_type',
        'entity_id',
        'error_code',
        'error_message',
        'error_context',
        'is_resolved',
        'resolution_note',
        'resolved_by',
        'resolved_at'
    ];

    protected $casts = [
        'error_context' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

    /**
     * Relacionamento com log de sincronização
     */
    public function syncLog()
    {
        return $this->belongsTo(HRSyncLog::class, 'sync_log_id'); // ✅ CORRIGIDO
    }

    /**
     * Relacionamento com usuário que resolveu
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scopes
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeByEntityType($query, $type)
    {
        return $query->where('entity_type', $type);
    }

    /**
     * Marcar como resolvido
     */
    public function resolve(string $note, int $userId): void
    {
        $this->update([
            'is_resolved' => true,
            'resolution_note' => $note,
            'resolved_by' => $userId,
            'resolved_at' => now()
        ]);
    }
}
