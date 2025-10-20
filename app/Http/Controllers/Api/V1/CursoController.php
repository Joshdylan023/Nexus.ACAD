<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\CatalogoCurso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\CursosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CursoController extends Controller
{
    /**
     * Lista todos os cursos com filtros avançados
     */
    public function index(Request $request): JsonResponse
    {
        $query = Curso::with([
            'catalogoCurso.grupoEducacional',
            'catalogoCurso.areaConhecimento',
            'instituicao',
            'campus',
            'areaConhecimento',
            'coordenador', // ✅ User (coordenador atual)
            'coordenadorTitularAtivo.colaborador.usuario', // ✅ NOVO: Coordenador da tabela coordenadores_curso
            'createdBy',
            'updatedBy'
        ]);

        // ==========================================
        // ✅ FILTROS HIERÁRQUICOS
        // ==========================================
        
        // Filtro por grupo educacional (via catálogo ou instituição)
        if ($request->filled('grupo_educacional_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('catalogoCurso', function($sq) use ($request) {
                    $sq->where('grupo_educacional_id', $request->grupo_educacional_id);
                })
                ->orWhereHas('instituicao.mantenedora', function($sq) use ($request) {
                    $sq->where('grupo_educacional_id', $request->grupo_educacional_id);
                });
            });
        }

        // Filtro por instituição
        if ($request->filled('instituicao_id')) {
            $query->where('instituicao_id', $request->instituicao_id);
        }

        // Filtro por campus
        if ($request->filled('campus_id')) {
            $query->where(function($q) use ($request) {
                $q->where('campus_id', $request->campus_id)
                  ->orWhereNull('campus_id');
            });
        }

        // Filtro por área de conhecimento
        if ($request->filled('area_conhecimento_id')) {
            $query->where('area_conhecimento_id', $request->area_conhecimento_id);
        }

        // ==========================================
        // ✅ FILTROS ADICIONAIS
        // ==========================================

        // Filtro por nível
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Filtro por modalidade
        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro: apenas cursos vinculados ao catálogo
        if ($request->filled('com_catalogo') && $request->com_catalogo == '1') {
            $query->whereNotNull('catalogo_curso_id');
        }

        // Filtro: apenas cursos sem catálogo (legados)
        if ($request->filled('sem_catalogo') && $request->sem_catalogo == '1') {
            $query->whereNull('catalogo_curso_id');
        }

        // ==========================================
        // ✅ BUSCA POR TEXTO
        // ==========================================
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo_ies', 'like', "%{$search}%");
            });
        }

        // ==========================================
        // ✅ ORDENAÇÃO E PAGINAÇÃO
        // ==========================================

        $sortBy = $request->get('sort_by', 'nome');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Ordenação especial para campos relacionados
        if ($sortBy === 'instituicao') {
            $query->join('instituicoes', 'cursos.instituicao_id', '=', 'instituicoes.id')
                  ->orderBy('instituicoes.nome_fantasia', $sortOrder)
                  ->select('cursos.*');
        } elseif ($sortBy === 'campus') {
            $query->leftJoin('campi', 'cursos.campus_id', '=', 'campi.id')
                  ->orderBy('campi.nome', $sortOrder)
                  ->select('cursos.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginação ou todos os resultados
        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    /**
     * Cria um novo curso
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            // ✅ NOVOS CAMPOS
            'catalogo_curso_id' => 'nullable|exists:catalogo_cursos,id',
            'campus_id' => 'nullable|exists:campi,id',
            'sigla' => 'nullable|string|max:10',
            'codigo_ies' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cursos')->where(function ($query) use ($request) {
                    return $query->where('instituicao_id', $request->instituicao_id);
                })
            ],
            'grau' => ['nullable', Rule::in([
                'Bacharelado', 'Licenciatura', 'Tecnólogo', 'Técnico', 
                'Especialista', 'Mestre', 'Doutor', 'Não se aplica'
            ])],
            'modalidade' => ['required', Rule::in(['presencial', 'ead', 'semipresencial'])],
            'carga_horaria_total' => 'nullable|integer|min:1',
            
            // ✅ CAMPOS EXISTENTES (MANTIDOS)
            'instituicao_id' => 'required|exists:instituicoes,id',
            'area_conhecimento_id' => 'required|exists:areas_conhecimento,id',
            'nome' => 'required|string|max:255',
            'nivel' => ['required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação', 
                'Especialização', 'Mestrado', 'Doutorado', 'Extensão', 'Livre',
            ])],
            'duracao_padrao_semestres' => 'required|integer|min:1',
            'prazo_maximo_semestres' => 'required|integer|min:1|gt:duracao_padrao_semestres',
            'coordenador_id' => 'nullable|exists:colaboradores,id', // ✅ CORRIGIDO: colaboradores, não users
            'status' => ['required', Rule::in([
                'Em Planejamento', 'Ativo', 'Em Extinção', 'Extinto'
            ])],
            'vagas_anuais' => 'required|integer|min:0',
        ], [
            'prazo_maximo_semestres.gt' => 'O prazo máximo deve ser maior que a duração padrão.',
            'codigo_ies.unique' => 'Este código já existe para esta instituição.'
        ]);

        DB::beginTransaction();
        try {
            // ✅ VALIDAÇÃO: Se vincular ao catálogo, verificar duplicação
            if ($request->filled('catalogo_curso_id')) {
                $exists = Curso::where('catalogo_curso_id', $request->catalogo_curso_id)
                    ->where('instituicao_id', $request->instituicao_id)
                    ->where('campus_id', $request->campus_id)
                    ->exists();
                
                if ($exists) {
                    return response()->json([
                        'message' => 'Este curso do catálogo já está cadastrado para esta instituição/campus.',
                        'errors' => ['catalogo_curso_id' => ['Curso duplicado']]
                    ], 422);
                }
            }

            // ✅ AUTO-PREENCHIMENTO: Se vier do catálogo, herda informações
            if ($request->filled('catalogo_curso_id')) {
                $catalogo = CatalogoCurso::find($request->catalogo_curso_id);
                if ($catalogo) {
                    $validatedData['nome'] = $validatedData['nome'] ?? $catalogo->nome;
                    $validatedData['sigla'] = $validatedData['sigla'] ?? $catalogo->sigla;
                    $validatedData['nivel'] = $validatedData['nivel'] ?? $catalogo->nivel;
                    $validatedData['grau'] = $validatedData['grau'] ?? $catalogo->grau;
                    $validatedData['modalidade'] = $validatedData['modalidade'] ?? $catalogo->modalidade;
                    $validatedData['duracao_padrao_semestres'] = $validatedData['duracao_padrao_semestres'] ?? $catalogo->duracao_padrao_semestres;
                    $validatedData['prazo_maximo_semestres'] = $validatedData['prazo_maximo_semestres'] ?? $catalogo->prazo_maximo_semestres;
                    $validatedData['carga_horaria_total'] = $validatedData['carga_horaria_total'] ?? $catalogo->carga_horaria_total;
                }
            }

            $validatedData['created_by'] = auth()->id();
            $validatedData['updated_by'] = auth()->id();

            $curso = Curso::create($validatedData);
            // ✅ Observer do Curso cuida da sincronização automaticamente
            
            DB::commit();

            Log::info('Curso criado', [
                'curso_id' => $curso->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Curso criado com sucesso!',
                'data' => $curso->load([
                    'catalogoCurso',
                    'instituicao',
                    'campus',
                    'areaConhecimento',
                    'coordenador',
                    'coordenadorTitularAtivo.colaborador.usuario'
                ])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar curso: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao criar curso',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Exibe um curso específico
     */
    public function show(Curso $curso): JsonResponse
    {
        return response()->json(
            $curso->load([
                'catalogoCurso.grupoEducacional',
                'catalogoCurso.areaConhecimento',
                'instituicao.mantenedora.grupoEducacional',
                'campus',
                'areaConhecimento.grandeArea',
                'coordenador',
                'coordenadorTitularAtivo.colaborador.usuario',
                'coordenadoresAtivos.colaborador.usuario',
                'atosRegulatorios',
                'createdBy',
                'updatedBy'
            ])
        );
    }

    /**
     * Atualiza um curso
     */
    public function update(Request $request, Curso $curso): JsonResponse
    {
        $validatedData = $request->validate([
            // ✅ NOVOS CAMPOS
            'catalogo_curso_id' => 'nullable|exists:catalogo_cursos,id',
            'campus_id' => 'nullable|exists:campi,id',
            'sigla' => 'nullable|string|max:10',
            'codigo_ies' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cursos')->where(function ($query) use ($request) {
                    return $query->where('instituicao_id', $request->instituicao_id);
                })->ignore($curso->id)
            ],
            'grau' => ['nullable', Rule::in([
                'Bacharelado', 'Licenciatura', 'Tecnólogo', 'Técnico',
                'Especialista', 'Mestre', 'Doutor', 'Não se aplica'
            ])],
            'modalidade' => ['required', Rule::in(['presencial', 'ead', 'semipresencial'])],
            'carga_horaria_total' => 'nullable|integer|min:1',
            
            // ✅ CAMPOS EXISTENTES (MANTIDOS)
            'instituicao_id' => 'required|exists:instituicoes,id',
            'area_conhecimento_id' => 'required|exists:areas_conhecimento,id',
            'nome' => 'required|string|max:255',
            'nivel' => ['required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação',
                'Especialização', 'Mestrado', 'Doutorado', 'Extensão', 'Livre',
            ])],
            'duracao_padrao_semestres' => 'required|integer|min:1',
            'prazo_maximo_semestres' => 'required|integer|min:1|gt:duracao_padrao_semestres',
            'coordenador_id' => 'nullable|exists:colaboradores,id', // ✅ CORRIGIDO
            'status' => ['required', Rule::in([
                'Em Planejamento', 'Ativo', 'Em Extinção', 'Extinto'
            ])],
            'vagas_anuais' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $validatedData['updated_by'] = auth()->id();
            
            $curso->update($validatedData);
            // ✅ Observer do Curso cuida da sincronização automaticamente
            
            DB::commit();

            Log::info('Curso atualizado', [
                'curso_id' => $curso->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Curso atualizado com sucesso!',
                'data' => $curso->load([
                    'catalogoCurso',
                    'instituicao',
                    'campus',
                    'areaConhecimento',
                    'coordenador',
                    'coordenadorTitularAtivo.colaborador.usuario'
                ])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar curso: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar curso',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Exclui um curso (soft delete)
     */
    public function destroy(Curso $curso): JsonResponse
    {
        // ✅ VALIDAÇÃO: Verificar dependências
        if ($curso->turmas()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este curso pois existem turmas vinculadas a ele.'
            ], 422);
        }

        if ($curso->matriculas()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este curso pois existem matrículas vinculadas a ele.'
            ], 422);
        }

        $curso->delete();

        return response()->json([
            'message' => 'Curso excluído com sucesso!'
        ], 200);
    }

    /**
     * ✅ NOVO: Sincroniza curso com o catálogo
     */
    public function sincronizarComCatalogo(Curso $curso): JsonResponse
    {
        if (!$curso->catalogo_curso_id) {
            return response()->json([
                'message' => 'Este curso não está vinculado a um catálogo.'
            ], 422);
        }

        $sucesso = $curso->sincronizarComCatalogo();

        if ($sucesso) {
            return response()->json([
                'message' => 'Curso sincronizado com sucesso!',
                'data' => $curso->fresh()->load(['catalogoCurso', 'instituicao', 'campus'])
            ]);
        }

        return response()->json([
            'message' => 'Erro ao sincronizar curso.'
        ], 500);
    }

    /**
     * ✅ NOVO: Lista cursos do catálogo disponíveis para uma instituição
     */
    public function catalogoDisponiveis(Request $request): JsonResponse
    {
        $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id'
        ]);

        $instituicao = \App\Models\Instituicao::with('mantenedora.grupoEducacional')->find($request->instituicao_id);
        
        if (!$instituicao || !$instituicao->mantenedora || !$instituicao->mantenedora->grupo_educacional_id) {
            return response()->json([
                'message' => 'Não foi possível determinar o grupo educacional desta instituição.'
            ], 422);
        }

        $grupoId = $instituicao->mantenedora->grupo_educacional_id;

        $cursosDisponiveis = CatalogoCurso::where('grupo_educacional_id', $grupoId)
            ->where('status', 'ativo')
            ->whereDoesntHave('cursos', function($q) use ($request) {
                $q->where('instituicao_id', $request->instituicao_id);
            })
            ->with(['areaConhecimento', 'grupoEducacional'])
            ->get();

        return response()->json($cursosDisponiveis);
    }

    /**
     * Exporta cursos para Excel com identidade visual
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['instituicao_id', 'nivel', 'modalidade', 'search']);
        
        $identidadeVisual = null;
        $instituicao = null;
        
        if ($request->filled('instituicao_id')) {
            $instituicao = \App\Models\Instituicao::find($request->instituicao_id);
            
            if ($instituicao) {
                $identidadeVisual = \App\Models\IdentidadeVisual::where('entidade_type', 'instituicao')
                    ->where('entidade_id', $instituicao->id)
                    ->first();
            }
        }
        
        return Excel::download(
            new CursosExport($filters, $identidadeVisual, $instituicao), 
            'cursos_' . date('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Exporta cursos para PDF com identidade visual da instituição
     */
    public function exportPDF(Request $request)
    {
        $query = Curso::with(['instituicao', 'areaConhecimento', 'coordenador']);

        // Aplica filtros
        if ($request->filled('instituicao_id')) {
            $query->where('instituicao_id', $request->instituicao_id);
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo_ies', 'like', "%{$search}%");
            });
        }

        $cursos = $query->get();

        // ✅ BUSCAR IDENTIDADE VISUAL COM FALLBACK
        $identidadeVisual = null;
        $instituicao = null;
        
        if ($request->filled('instituicao_id')) {
            $instituicao = \App\Models\Instituicao::find($request->instituicao_id);
            
            if ($instituicao) {
                $identidadeVisual = \App\Models\IdentidadeVisual::where('entidade_type', 'App\\Models\\Instituicao')
                    ->where('entidade_id', $instituicao->id)
                    ->first();
            }
        }

        if (!$identidadeVisual && $instituicao && $instituicao->mantenedora) {
            $identidadeVisual = \App\Models\IdentidadeVisual::where('entidade_type', 'App\\Models\\GrupoEducacional')
                ->where('entidade_id', $instituicao->mantenedora->grupo_educacional_id)
                ->first();
        }

        if (!$identidadeVisual) {
            $identidadeVisual = \App\Models\IdentidadeVisual::first();
        }

        if (!$identidadeVisual) {
            $identidadeVisual = (object) [
                'logo_principal' => null,
                'cor_primaria' => '#667eea',
                'cor_secundaria' => '#10b981'
            ];
        }

        $pdf = Pdf::loadView('exports.cursos-pdf', [
            'cursos' => $cursos,
            'data_geracao' => now()->format('d/m/Y H:i:s'),
            'identidadeVisual' => $identidadeVisual,
            'instituicao' => $instituicao,
            'filtros' => [
                'instituicao' => $instituicao ? $instituicao->nome_fantasia : 'Todas',
                'nivel' => $request->nivel ?? 'Todos',
                'modalidade' => $request->modalidade ?? 'Todas',
            ]
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('cursos_' . date('Y-m-d_His') . '.pdf');
    }
}
