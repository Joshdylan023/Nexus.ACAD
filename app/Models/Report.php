<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'description',
        'type',
        'filters',
        'columns',
        'format',
        'is_public',
        'is_scheduled',
        'schedule_frequency',
        'last_executed_at',
        'execution_count'
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'is_public' => 'boolean',
        'is_scheduled' => 'boolean',
        'last_executed_at' => 'datetime'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function incrementExecutionCount()
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }
}
