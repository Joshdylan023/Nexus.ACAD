<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\SystemEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportLog extends Model
{
    protected $fillable = [
        'system_event_id',
        'user_id',
        'import_type',
        'file_name',
        'status',
        'total_rows',
        'success_count',
        'error_count',
        'errors',
        'summary',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'errors' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function systemEvent(): BelongsTo
    {
        return $this->belongsTo(SystemEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return round(($this->success_count / $this->total_rows) * 100, 2);
    }
}
