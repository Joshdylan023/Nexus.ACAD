<?php

namespace App\Traits;

trait Auditable
{
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

        // Ao DELETAR (soft delete) um registro - SOMENTE se tiver SoftDeletes
        static::deleting(function ($model) {
            if (auth()->check() && method_exists($model, 'isForceDeleting') && $model->isForceDeleting() === false) {
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
     * Scope para buscar apenas registros não deletados (se tiver SoftDeletes)
     */
    public function scopeActive($query)
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this))) {
            return $query->whereNull('deleted_at');
        }
        return $query;
    }

    /**
     * Scope para buscar registros deletados (se tiver SoftDeletes)
     */
    public function scopeOnlyTrashed($query)
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this))) {
            return $query->whereNotNull('deleted_at');
        }
        return $query;
    }
}
