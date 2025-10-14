<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait Auditable
{
    use SoftDeletes;

    /**
     * Boot do Trait - registra eventos
     */
    protected static function bootAuditable()
    {
        // Ao CRIAR um registro
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        // Ao ATUALIZAR um registro
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Ao DELETAR (soft delete) um registro
        static::deleting(function ($model) {
            if (auth()->check() && $model->isForceDeleting() === false) {
                $model->updated_by = auth()->id();
                $model->save();
            }
        });
    }

    /**
     * Relacionamento: Usuário que criou
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relacionamento: Usuário que atualizou
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope para buscar apenas registros não deletados
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para buscar registros deletados
     */
    public function scopeOnlyTrashed($query)
    {
        return $query->whereNotNull('deleted_at');
    }
}
