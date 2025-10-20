<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class HRIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_integrations';

    protected $fillable = [
        'name',
        'provider',
        'is_active',
        'config',
        'field_mapping',
        'sync_frequency',
        'sync_time',
        'sync_day',
        'auto_sync_enabled',
        'last_sync_at',
        'next_sync_at',
        'sync_options',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'field_mapping' => 'array',
        'sync_options' => 'array',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ✅ NÃO esconda config do JSON (precisamos dele no frontend)
    // protected $hidden = ['config'];

    /**
     * Boot do model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-preencher created_by e updated_by
        static::creating(function ($model) {
            if (!$model->created_by) {
                $model->created_by = auth()->id() ?? 1;
            }
            if (!$model->updated_by) {
                $model->updated_by = auth()->id() ?? 1;
            }
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id() ?? 1;
        });
    }

    /**
     * ✅ Accessor para descriptografar config (COM TRATAMENTO DE ERRO)
     */
    public function getConfigAttribute($value)
    {
        if (!$value) return [];
        
        try {
            // Tentar descriptografar
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true) ?? [];
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Se falhar, tentar como JSON direto (pode estar sem criptografia)
            Log::warning('Config não criptografado, tentando JSON direto', [
                'integration_id' => $this->id ?? 'novo'
            ]);
            
            $json = json_decode($value, true);
            return is_array($json) ? $json : [];
        } catch (\Exception $e) {
            Log::error('Erro ao descriptografar config: ' . $e->getMessage(), [
                'integration_id' => $this->id ?? 'novo'
            ]);
            return [];
        }
    }

    /**
     * ✅ Mutator para criptografar config (COM TRATAMENTO DE ERRO)
     */
    public function setConfigAttribute($value)
    {
        if (!$value || (is_array($value) && empty($value))) {
            $this->attributes['config'] = null;
            return;
        }
        
        try {
            $json = is_array($value) ? json_encode($value) : $value;
            $this->attributes['config'] = Crypt::encryptString($json);
        } catch (\Exception $e) {
            Log::error('Erro ao criptografar config: ' . $e->getMessage());
            // Fallback: salvar como JSON sem criptografia
            $this->attributes['config'] = is_array($value) ? json_encode($value) : $value;
        }
    }

    /**
     * Relacionamento com usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário que atualizou
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relacionamento com logs de sincronização
     */
    public function syncLogs()
    {
        return $this->hasMany(HRSyncLog::class, 'hr_integration_id')->orderBy('created_at', 'desc');
    }

    /**
     * Último log de sincronização
     */
    public function lastSyncLog()
    {
        return $this->hasOne(HRSyncLog::class, 'hr_integration_id')->latestOfMany();
    }

    /**
     * Logs de sincronização com falha
     */
    public function failedSyncLogs()
    {
        return $this->hasMany(HRSyncLog::class, 'hr_integration_id')->where('status', 'failed');
    }

    /**
     * Relacionamento com colaboradores sincronizados
     */
    public function colaboradores()
    {
        return $this->hasMany(\App\Models\Colaborador::class, 'hr_integration_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeAutoSyncEnabled($query)
    {
        return $query->where('auto_sync_enabled', true);
    }

    /**
     * Verificar se precisa sincronizar
     */
    public function needsSync(): bool
    {
        if (!$this->is_active) return false;
        if (!$this->auto_sync_enabled) return false;
        if (!$this->next_sync_at) return true;
        
        return $this->next_sync_at->isPast();
    }

    /**
     * Calcular próxima sincronização
     */
    public function calculateNextSync(): void
    {
        if (!$this->auto_sync_enabled || $this->sync_frequency === 'manual') {
            $this->next_sync_at = null;
            return;
        }

        $now = now();

        $this->next_sync_at = match($this->sync_frequency) {
            'hourly' => $now->addHour()->startOfHour(),
            'daily' => $this->getNextDailySync($now),
            'weekly' => $this->getNextWeeklySync($now),
            'monthly' => $now->addMonth()->startOfMonth()->setTime(2, 0, 0),
            default => null
        };
    }

    /**
     * Calcular próxima sincronização diária
     */
    protected function getNextDailySync($now)
    {
        $time = $this->sync_time ?? '02:00';
        [$hours, $minutes] = explode(':', $time);
        
        $next = $now->copy()->setTime((int)$hours, (int)$minutes, 0);
        
        // Se já passou do horário hoje, agendar para amanhã
        if ($next->isPast()) {
            $next->addDay();
        }
        
        return $next;
    }

    /**
     * Calcular próxima sincronização semanal
     */
    protected function getNextWeeklySync($now)
    {
        $day = $this->sync_day ?? 0; // 0 = domingo
        $time = $this->sync_time ?? '03:00';
        [$hours, $minutes] = explode(':', $time);
        
        // Encontrar o próximo dia da semana especificado
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $targetDay = $dayNames[$day] ?? 'Sunday';
        
        $next = $now->copy()->next($targetDay)->setTime((int)$hours, (int)$minutes, 0);
        
        return $next;
    }

    /**
     * ✅ Obter nome amigável do provider (ACCESSOR)
     */
    public function getProviderNameAttribute(): string
    {
        return match($this->attributes['provider'] ?? 'generic') {
            'generic' => 'API Genérica',
            'totvs' => 'TOTVS Protheus',
            'sap' => 'SAP SuccessFactors',
            'oracle' => 'Oracle HCM Cloud',
            'senior' => 'Senior X',
            'adp' => 'ADP Workforce Now',
            'adp_expert' => 'ADP eXpert',
            'csv' => 'Importação CSV/Excel',
            default => ucfirst($this->attributes['provider'] ?? 'Desconhecido')
        };
    }

    /**
     * Status da última sincronização
     */
    public function getLastSyncStatusAttribute(): ?string
    {
        return $this->lastSyncLog?->status;
    }

    /**
     * Cor do status (para UI)
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->last_sync_status) {
            'completed' => 'success',
            'running' => 'info',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Ícone do status (para UI)
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->last_sync_status) {
            'completed' => 'check-circle',
            'running' => 'arrow-repeat',
            'failed' => 'exclamation-triangle',
            default => 'question-circle'
        };
    }

    /**
     * Estatísticas de sincronização
     */
    public function getSyncStatsAttribute(): array
    {
        return [
            'total_syncs' => $this->syncLogs()->count(),
            'successful_syncs' => $this->syncLogs()->where('status', 'completed')->count(),
            'failed_syncs' => $this->syncLogs()->where('status', 'failed')->count(),
            'last_sync_at' => $this->last_sync_at?->toDateTimeString(),
            'next_sync_at' => $this->next_sync_at?->toDateTimeString(),
            'last_status' => $this->last_sync_status,
        ];
    }

    /**
     * Marcar como sincronizando agora
     */
    public function markSyncing(): void
    {
        $this->update([
            'last_sync_at' => now()
        ]);
    }

    /**
     * Validar configuração
     */
    public function validateConfig(): array
    {
        $errors = [];
        
        if (!$this->config || empty($this->config)) {
            $errors[] = 'Configuração não pode estar vazia';
            return $errors;
        }
        
        // Validações específicas por provider
        switch ($this->provider) {
            case 'csv':
                if (empty($this->config['delimiter'])) {
                    $errors[] = 'Delimitador é obrigatório para CSV';
                }
                break;
                
            case 'totvs':
            case 'sap':
            case 'oracle':
                if (empty($this->config['base_url'])) {
                    $errors[] = 'URL base é obrigatória';
                }
                if (empty($this->config['client_id'])) {
                    $errors[] = 'Client ID é obrigatório';
                }
                break;
        }
        
        return $errors;
    }

    /**
     * Validar mapeamento de campos
     */
    public function validateFieldMapping(): array
    {
        $errors = [];
        
        if (empty($this->field_mapping)) {
            // Não é obrigatório ter mapeamento (pode usar padrão)
            return [];
        }
        
        // Verificar campos obrigatórios
        $requiredFields = ['matricula_funcional', 'nome_completo'];
        $mappedFields = array_column($this->field_mapping, 'target_field');
        
        foreach ($requiredFields as $field) {
            if (!in_array($field, $mappedFields)) {
                $errors[] = "Campo obrigatório '{$field}' não está mapeado";
            }
        }
        
        return $errors;
    }

    /**
     * Verificar se pode sincronizar
     */
    public function canSync(): bool
    {
        return $this->is_active && empty($this->validateConfig());
    }

    /**
     * ✅ Serialização customizada para JSON (para API)
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Garantir que config está no formato correto
        if (isset($array['config']) && is_string($array['config'])) {
            $array['config'] = $this->config; // Usar accessor
        }
        
        return $array;
    }
}
