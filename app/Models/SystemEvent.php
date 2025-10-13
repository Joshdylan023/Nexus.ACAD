<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SystemEvent extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'status',
        'start_at',
        'end_at',
        'created_by',
        'restricted_access',
        'block_student_portal',
        'block_teacher_portal',
        'block_admin_portal'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'restricted_access' => 'array',
        'block_student_portal' => 'boolean',
        'block_teacher_portal' => 'boolean',
        'block_admin_portal' => 'boolean'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               Carbon::now()->between($this->start_at, $this->end_at ?? Carbon::now()->addYears(10));
    }

    public function canUserAccess(int $userId): bool
    {
        if (!$this->isActive()) {
            return true;
        }

        // Super admin sempre tem acesso
        $user = User::find($userId);
        if ($user && $user->hasRole('super-admin')) {
            return true;
        }

        // Verifica se usuário está na lista de acesso restrito
        $restrictedAccess = $this->restricted_access ?? [];
        return in_array($userId, $restrictedAccess);
    }

    public static function getActiveEvent()
    {
        return self::where('status', 'active')
            ->where('start_at', '<=', Carbon::now())
            ->where(function ($query) {
                $query->whereNull('end_at')
                      ->orWhere('end_at', '>=', Carbon::now());
            })
            ->first();
    }
}
