<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Str;

trait LogsActivity
{
    /**
     * Boot do Trait - registra eventos de auditoria
     */
    protected static function bootLogsActivity()
    {
        // Ao CRIAR um registro
        static::created(function ($model) {
            $model->logActivity('created');
        });

        // Ao ATUALIZAR um registro
        static::updated(function ($model) {
            if ($model->shouldLogUpdate()) {
                $model->logActivity('updated');
            }
        });

        // Ao DELETAR um registro
        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });

        // Ao RESTAURAR (soft delete)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logActivity('restored');
            });
        }
    }

    /**
     * Criar log de atividade
     */
    protected function logActivity(string $action): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => $this->getLogModule(),
                'action' => $action,
                'auditable_type' => get_class($this),
                'auditable_id' => $this->id,
                'old_values' => $action === 'updated' ? $this->getOriginal() : null,
                'new_values' => $action !== 'deleted' ? $this->getAttributes() : null,
                'description' => $this->getLogDescription($action),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar log de auditoria: ' . $e->getMessage());
        }
    }

    /**
     * Verifica se deve logar a atualização
     * (evita logar alterações em campos como updated_at, etc.)
     */
    protected function shouldLogUpdate(): bool
    {
        $ignoredFields = ['updated_at', 'remember_token'];
        $dirtyFields = array_keys($this->getDirty());
        
        // Se houver alterações além dos campos ignorados
        $relevantChanges = array_diff($dirtyFields, $ignoredFields);
        
        return count($relevantChanges) > 0;
    }

    /**
     * Define o módulo do log (deve ser implementado em cada Model)
     */
    protected function getLogModule(): string
    {
        // Implementar em cada Model
        return 'sistema';
    }

    /**
     * Gera descrição legível da ação (pode ser customizado em cada Model)
     */
    protected function getLogDescription(string $action): string
    {
        $userName = auth()->user()?->name ?? 'Sistema';
        $modelName = $this->getModelFriendlyName();
        $identifier = $this->getLogIdentifier();
        
        return match($action) {
            'created' => "{$userName} criou {$modelName}: {$identifier}",
            'updated' => "{$userName} atualizou {$modelName}: {$identifier}",
            'deleted' => "{$userName} removeu {$modelName}: {$identifier}",
            'restored' => "{$userName} restaurou {$modelName}: {$identifier}",
            default => "{$userName} realizou uma ação em {$modelName}: {$identifier}",
        };
    }

    /**
     * Nome amigável do Model (pode ser sobrescrito)
     */
    protected function getModelFriendlyName(): string
    {
        return class_basename($this);
    }

    /**
     * Identificador do registro (pode ser sobrescrito)
     */
    protected function getLogIdentifier(): string
    {
        // Tenta usar campos comuns de identificação
        if (isset($this->nome)) return $this->nome;
        if (isset($this->name)) return $this->name;
        if (isset($this->razao_social)) return $this->razao_social;
        if (isset($this->nome_fantasia)) return $this->nome_fantasia;
        if (isset($this->titulo)) return $this->titulo;
        
        return "#{$this->id}";
    }

    /**
     * Relacionamento com logs de auditoria
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }
}
