<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relacionamento: Usuário que realizou a ação
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico: Entidade auditada
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Filtrar por módulo
     */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Filtrar por ação
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filtrar por tipo de entidade
     */
    public function scopeAuditableType($query, string $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Scope: Filtrar por usuário
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Busca textual
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('description', 'ILIKE', "%{$search}%")
              ->orWhereHas('user', function($q2) use ($search) {
                  $q2->where('name', 'ILIKE', "%{$search}%");
              });
        });
    }

    /**
     * Accessor: Nome do módulo traduzido
     */
    public function getModuleNameAttribute(): string
    {
        return match($this->module) {
            'institucional' => 'Institucional',
            'pessoas_acessos' => 'Pessoas & Acessos',
            'academico' => 'Acadêmico',
            'financeiro' => 'Financeiro',
            'estagios' => 'Estágios',
            'sistema' => 'Sistema',
            default => ucfirst($this->module),
        };
    }

    /**
     * Accessor: Nome da ação traduzido
     */
    public function getActionNameAttribute(): string
    {
        return match($this->action) {
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Exclusão',
            'restored' => 'Restauração',
            default => ucfirst($this->action),
        };
    }

    /**
     * Accessor: Badge color por ação
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match($this->action) {
            'created' => 'bg-success',
            'updated' => 'bg-info',
            'deleted' => 'bg-danger',
            'restored' => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    /**
     * Retorna as mudanças (diff) formatadas
     */
    public function getChanges(): array
    {
        if ($this->action !== 'updated' || !$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            
            if ($oldValue != $newValue && !in_array($key, ['updated_at', 'updated_by'])) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
