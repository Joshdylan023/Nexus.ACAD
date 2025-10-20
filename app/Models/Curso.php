<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cursos';

    protected $fillable = [
        // ✅ Novos campos
        'catalogo_curso_id',
        'campus_id',
        'sigla',
        'codigo_ies',
        'grau',
        'modalidade',
        'carga_horaria_total',
        'created_by',
        'updated_by',
        
        // ✅ Campos existentes (mantidos)
        'instituicao_id',
        'area_conhecimento_id',
        'nome',
        'nivel',
        'duracao_padrao_semestres',
        'prazo_maximo_semestres',
        'coordenador_id',
        'status',
        'vagas_anuais',
    ];

    protected $casts = [
        'duracao_padrao_semestres' => 'integer',
        'prazo_maximo_semestres' => 'integer',
        'carga_horaria_total' => 'integer',
        'vagas_anuais' => 'integer'
    ];

    protected $appends = [
        'codigo_principal', 
        'codigo_completo', 
        'nome_completo'
    ];

    // ==========================================
    // ✅ RELACIONAMENTOS EXISTENTES (MANTIDOS)
    // ==========================================

    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }

    public function areaConhecimento(): BelongsTo
    {
        return $this->belongsTo(AreaConhecimento::class);
    }

    public function coordenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordenador_id');
    }

    public function atosRegulatorios(): HasMany
    {
        return $this->hasMany(CursosAtoRegulatorio::class);
    }

    // ==========================================
    // ✅ NOVOS RELACIONAMENTOS
    // ==========================================

    /**
     * Relacionamento com o catálogo de cursos do grupo
     */
    public function catalogoCurso(): BelongsTo
    {
        return $this->belongsTo(CatalogoCurso::class, 'catalogo_curso_id');
    }

    /**
     * Campus específico (opcional)
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Usuário que criou o registro
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Último usuário que atualizou o registro
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Turmas vinculadas ao curso
     */
    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }

    /**
     * Relacionamentos com os coordenadores do curso (tabela coordenadores_curso)
     */
    public function coordenadores(): HasMany
    {
        return $this->hasMany(CoordenadorCurso::class);
    }

    /**
     * Retorna os coordenadores que estão atualmente ativos para o curso.
     */
    public function coordenadoresAtivos(): HasMany
    {
        return $this->hasMany(CoordenadorCurso::class)->where('status', 'ativo');
    }

    /**
     * Retorna o coordenador titular que está atualmente ativo para o curso.
     */
    public function coordenadorTitularAtivo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CoordenadorCurso::class)
            ->where('tipo', 'titular')
            ->where('status', 'ativo');
    }


    /**
     * Matrículas vinculadas ao curso
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    // ==========================================
    // ✅ ACCESSORS (GETTERS)
    // ==========================================

    /**
     * Retorna o código principal do curso
     * Prioridade: catálogo > código IES > fallback
     */
    public function getCodigoPrincipalAttribute(): string
    {
        // Se tiver catálogo, usa o código do catálogo
        if ($this->catalogoCurso) {
            return $this->catalogoCurso->codigo;
        }
        
        // Se não tiver catálogo, usa o código IES
        if ($this->codigo_ies) {
            return $this->codigo_ies;
        }
        
        // Fallback para compatibilidade com código legado
        return 'CURSO-' . $this->id;
    }

    /**
     * Retorna o código completo (principal + IES se diferente)
     * Ex: "ENG-001" ou "ENG-001 (ENG-SSA-001)"
     */
    public function getCodigoCompletoAttribute(): string
    {
        $principal = $this->codigo_principal;
        $ies = $this->codigo_ies;
        
        // Se tem catálogo E código IES diferente, mostra ambos
        if ($this->catalogoCurso && $ies && $ies !== $principal) {
            return "{$principal} ({$ies})";
        }
        
        return $principal;
    }

    /**
     * Retorna o nome completo do curso com contexto
     * Ex: "Engenharia Civil - Campus Salvador" ou "Engenharia Civil - UNIFACS"
     */
    public function getNomeCompletoAttribute(): string
    {
        $nome = $this->nome;
        
        // Se tiver campus específico
        if ($this->campus) {
            return "{$nome} - {$this->campus->nome}";
        }
        
        // Se não tiver campus, mostra a instituição
        if ($this->instituicao) {
            return "{$nome} - {$this->instituicao->sigla}";
        }
        
        return $nome;
    }

    // ==========================================
    // ✅ SCOPES (FILTROS DE QUERY)
    // ==========================================

    /**
     * Filtrar apenas cursos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'Ativo');
    }

    /**
     * Filtrar por instituição
     */
    public function scopePorInstituicao($query, $instituicaoId)
    {
        return $query->where('instituicao_id', $instituicaoId);
    }

    /**
     * Filtrar por campus
     */
    public function scopePorCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    /**
     * Filtrar por nível de ensino
     */
    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    /**
     * Filtrar por modalidade
     */
    public function scopePorModalidade($query, $modalidade)
    {
        return $query->where('modalidade', $modalidade);
    }

    /**
     * Filtrar cursos que estão vinculados ao catálogo
     */
    public function scopeComCatalogo($query)
    {
        return $query->whereNotNull('catalogo_curso_id');
    }

    /**
     * Filtrar cursos que NÃO estão vinculados ao catálogo (legados/custom)
     */
    public function scopeSemCatalogo($query)
    {
        return $query->whereNull('catalogo_curso_id');
    }

    /**
     * Busca por texto (nome, código IES ou código do catálogo)
     */
    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('codigo_ies', 'like', "%{$termo}%")
              ->orWhere('sigla', 'like', "%{$termo}%")
              ->orWhereHas('catalogoCurso', function($sq) use ($termo) {
                  $sq->where('codigo', 'like', "%{$termo}%")
                     ->orWhere('nome', 'like', "%{$termo}%")
                     ->orWhere('sigla', 'like', "%{$termo}%");
              });
        });
    }

    // ==========================================
    // ✅ MÉTODOS AUXILIARES
    // ==========================================

    /**
     * Verifica se o curso está vinculado ao catálogo
     */
    public function vinculadoAoCatalogo(): bool
    {
        return !is_null($this->catalogo_curso_id);
    }

    /**
     * Verifica se o curso é específico de um campus
     */
    public function especificoDeCampus(): bool
    {
        return !is_null($this->campus_id);
    }

    /**
     * Verifica se o curso está disponível em todos os campi da instituição
     */
    public function disponivelEmTodosCampi(): bool
    {
        return is_null($this->campus_id);
    }

    /**
     * Retorna o grupo educacional
     * (via catálogo ou via instituição)
     */
    public function getGrupoEducacional()
    {
        // Tenta buscar via catálogo primeiro
        if ($this->catalogoCurso) {
            return $this->catalogoCurso->grupoEducacional;
        }
        
        // Se não tiver catálogo, busca via instituição
        if ($this->instituicao) {
            return $this->instituicao->mantenedora?->grupoEducacional;
        }
        
        return null;
    }

    /**
     * Retorna informações herdadas do catálogo
     * (se o curso estiver vinculado ao catálogo)
     */
    public function getInformacoesDoCatalogo()
    {
        if (!$this->catalogoCurso) {
            return null;
        }

        return [
            'codigo' => $this->catalogoCurso->codigo,
            'nome_padrao' => $this->catalogoCurso->nome,
            'descricao' => $this->catalogoCurso->descricao,
            'objetivo' => $this->catalogoCurso->objetivo,
            'perfil_egresso' => $this->catalogoCurso->perfil_egresso,
        ];
    }

    /**
     * Sincroniza informações do catálogo
     * (útil para manter cursos atualizados com o catálogo)
     */
    public function sincronizarComCatalogo(): bool
    {
        if (!$this->catalogoCurso) {
            return false;
        }

        $this->update([
            'nome' => $this->catalogoCurso->nome,
            'nivel' => $this->catalogoCurso->nivel,
            'grau' => $this->catalogoCurso->grau,
            'modalidade' => $this->catalogoCurso->modalidade,
            'duracao_padrao_semestres' => $this->catalogoCurso->duracao_padrao_semestres,
            'prazo_maximo_semestres' => $this->catalogoCurso->prazo_maximo_semestres,
            'carga_horaria_total' => $this->catalogoCurso->carga_horaria_total,
        ]);

        return true;
    }

    // ==========================================
    // ✅ EVENTOS DO MODEL
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        // Ao criar um curso
        static::creating(function ($curso) {
            // Se estiver autenticado, preenche created_by
            if (auth()->check()) {
                $curso->created_by = auth()->id();
            }

            // Se for vinculado ao catálogo e não tiver nome, copia do catálogo
            if ($curso->catalogo_curso_id && !$curso->nome) {
                $catalogo = CatalogoCurso::find($curso->catalogo_curso_id);
                if ($catalogo) {
                    $curso->nome = $catalogo->nome;
                    $curso->nivel = $catalogo->nivel;
                    $curso->grau = $catalogo->grau;
                    $curso->modalidade = $catalogo->modalidade;
                }
            }
        });

        // Ao atualizar um curso
        static::updating(function ($curso) {
            if (auth()->check()) {
                $curso->updated_by = auth()->id();
            }
        });
    }
}
