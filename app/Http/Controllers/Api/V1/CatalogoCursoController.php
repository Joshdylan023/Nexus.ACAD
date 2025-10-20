<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatalogoCurso;
use App\Models\GrupoEducacional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CatalogoCursoController extends Controller
{
    /**
     * Lista todos os cursos do catálogo com filtros avançados
     */
    public function index(Request $request): JsonResponse
    {
        $query = CatalogoCurso::with([
            'grupoEducacional',
            'areaConhecimento.grandeArea',
            'createdBy',
            'updatedBy'
        ]);

        // ==========================================
        // FILTROS
        // ==========================================

        // Filtro por grupo educacional
        if ($request->filled('grupo_educacional_id')) {
            $query->where('grupo_educacional_id', $request->grupo_educacional_id);
        }

        // Filtro por área de conhecimento
        if ($request->filled('area_conhecimento_id')) {
            $query->where('area_conhecimento_id', $request->area_conhecimento_id);
        }

        // Filtro por grande área (via área de conhecimento)
        if ($request->filled('grande_area_id')) {
            $query->whereHas('areaConhecimento', function($q) use ($request) {
                $q->where('grande_area_conhecimento_id', $request->grande_area_id);
            });
        }

        // Filtro por nível
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Filtro por grau
        if ($request->filled('grau')) {
            $query->where('grau', $request->grau);
        }

        // Filtro por modalidade
        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Por padrão, mostrar apenas ativos
            $query->where('status', 'ativo');
        }

        // ==========================================
        // BUSCA POR TEXTO
        // ==========================================

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nome', 'like', "%{$search}%")
                  ->orWhere('sigla', 'like', "%{$search}%")
                  ->orWhereHas('areaConhecimento', function($sq) use ($search) {
                      $sq->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        // ==========================================
        // FILTROS ESPECIAIS
        // ==========================================

        // Apenas cursos que ainda não foram cadastrados em nenhuma IES
        if ($request->filled('sem_uso') && $request->sem_uso == '1') {
            $query->has('cursos', '=', 0);
        }

        // Apenas cursos já cadastrados em pelo menos 1 IES
        if ($request->filled('em_uso') && $request->em_uso == '1') {
            $query->has('cursos', '>', 0);
        }

        // ==========================================
        // ESTATÍSTICAS ADICIONAIS
        // ==========================================

        // Contar quantas IES usam cada curso
        if ($request->filled('with_stats') && $request->with_stats == '1') {
            $query->withCount('cursos as total_instituicoes');
        }

        // ==========================================
        // ORDENAÇÃO E PAGINAÇÃO
        // ==========================================

        $sortBy = $request->get('sort_by', 'codigo');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Ordenação especial para campos relacionados
        if ($sortBy === 'grupo') {
            $query->join('grupos_educacionais', 'catalogo_cursos.grupo_educacional_id', '=', 'grupos_educacionais.id')
                  ->orderBy('grupos_educacionais.nome', $sortOrder)
                  ->select('catalogo_cursos.*');
        } elseif ($sortBy === 'area') {
            $query->join('areas_conhecimento', 'catalogo_cursos.area_conhecimento_id', '=', 'areas_conhecimento.id')
                  ->orderBy('areas_conhecimento.nome', $sortOrder)
                  ->select('catalogo_cursos.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginação
        $perPage = $request->get('per_page', 15);
        
        return response()->json($query->paginate($perPage));
    }

    /**
     * Cria um novo curso no catálogo
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'grupo_educacional_id' => 'required|exists:grupos_educacionais,id',
            'area_conhecimento_id' => 'required|exists:areas_conhecimento,id',
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('catalogo_cursos')->where(function ($query) use ($request) {
                    return $query->where('grupo_educacional_id', $request->grupo_educacional_id);
                })
            ],
            'nome' => 'required|string|max:200',
            'sigla' => 'nullable|string|max:10',
            'nivel' => ['required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação',
                'Especialização', 'Mestrado', 'Doutorado', 'Extensão', 'Livre'
            ])],
            'grau' => ['nullable', Rule::in([
                'Bacharelado', 'Licenciatura', 'Tecnólogo', 'Técnico',
                'Especialista', 'Mestre', 'Doutor', 'Não se aplica'
            ])],
            'modalidade' => ['required', Rule::in(['presencial', 'ead', 'semipresencial'])],
            'duracao_padrao_semestres' => 'nullable|integer|min:1|max:20',
            'prazo_maximo_semestres' => 'nullable|integer|min:1|max:30|gt:duracao_padrao_semestres',
            'carga_horaria_total' => 'nullable|integer|min:1|max:10000',
            'descricao' => 'nullable|string|max:5000',
            'objetivo' => 'nullable|string|max:5000',
            'perfil_egresso' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(['ativo', 'inativo'])],
        ], [
            'codigo.unique' => 'Este código já existe neste grupo educacional.',
            'prazo_maximo_semestres.gt' => 'O prazo máximo deve ser maior que a duração padrão.'
        ]);

        DB::beginTransaction();
        try {
            $catalogoCurso = CatalogoCurso::create($validatedData);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Curso adicionado ao catálogo com sucesso!',
                'data' => $catalogoCurso->load([
                    'grupoEducacional',
                    'areaConhecimento.grandeArea'
                ])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar curso no catálogo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe um curso específico do catálogo
     */
    public function show(CatalogoCurso $catalogoCurso): JsonResponse
    {
        return response()->json(
            $catalogoCurso->load([
                'grupoEducacional',
                'areaConhecimento.grandeArea',
                'cursos.instituicao',
                'cursos.campus',
                'createdBy',
                'updatedBy'
            ])
        );
    }

    /**
     * Atualiza um curso do catálogo
     */
    public function update(Request $request, CatalogoCurso $catalogoCurso): JsonResponse
    {
        $validatedData = $request->validate([
            'grupo_educacional_id' => 'sometimes|required|exists:grupos_educacionais,id',
            'area_conhecimento_id' => 'sometimes|required|exists:areas_conhecimento,id',
            'codigo' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('catalogo_cursos')->where(function ($query) use ($request) {
                    return $query->where('grupo_educacional_id', $request->grupo_educacional_id);
                })->ignore($catalogoCurso->id)
            ],
            'nome' => 'sometimes|required|string|max:200',
            'sigla' => 'nullable|string|max:10',
            'nivel' => ['sometimes', 'required', Rule::in([
                'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação',
                'Especialização', 'Mestrado', 'Doutorado', 'Extensão', 'Livre'
            ])],
            'grau' => ['nullable', Rule::in([
                'Bacharelado', 'Licenciatura', 'Tecnólogo', 'Técnico',
                'Especialista', 'Mestre', 'Doutor', 'Não se aplica'
            ])],
            'modalidade' => ['sometimes', 'required', Rule::in(['presencial', 'ead', 'semipresencial'])],
            'duracao_padrao_semestres' => 'nullable|integer|min:1|max:20',
            'prazo_maximo_semestres' => 'nullable|integer|min:1|max:30|gt:duracao_padrao_semestres',
            'carga_horaria_total' => 'nullable|integer|min:1|max:10000',
            'descricao' => 'nullable|string|max:5000',
            'objetivo' => 'nullable|string|max:5000',
            'perfil_egresso' => 'nullable|string|max:5000',
            'status' => ['sometimes', 'required', Rule::in(['ativo', 'inativo'])],
        ]);

        DB::beginTransaction();
        try {
            $catalogoCurso->update($validatedData);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Curso do catálogo atualizado com sucesso!',
                'data' => $catalogoCurso->load([
                    'grupoEducacional',
                    'areaConhecimento.grandeArea'
                ])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar curso do catálogo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exclui um curso do catálogo (soft delete)
     */
    public function destroy(CatalogoCurso $catalogoCurso): JsonResponse
    {
        // Verificar se há cursos vinculados
        $totalCursosVinculados = $catalogoCurso->cursos()->count();
        
        if ($totalCursosVinculados > 0) {
            return response()->json([
                'message' => "Não é possível excluir este curso do catálogo pois existem {$totalCursosVinculados} curso(s) vinculado(s) a ele.",
                'total_vinculos' => $totalCursosVinculados,
                'cursos_vinculados' => $catalogoCurso->cursos()
                    ->with('instituicao:id,nome_fantasia')
                    ->get(['id', 'nome', 'instituicao_id'])
            ], 422);
        }

        $catalogoCurso->delete();

        return response()->json([
            'message' => 'Curso do catálogo excluído com sucesso!'
        ], 200);
    }

    /**
     * ✅ NOVO: Duplicar curso do catálogo para outro grupo
     */
    public function duplicar(Request $request, CatalogoCurso $catalogoCurso): JsonResponse
    {
        $request->validate([
            'grupo_educacional_id' => 'required|exists:grupos_educacionais,id',
            'novo_codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('catalogo_cursos', 'codigo')->where(function ($query) use ($request) {
                    return $query->where('grupo_educacional_id', $request->grupo_educacional_id);
                })
            ]
        ]);

        DB::beginTransaction();
        try {
            $novoCurso = $catalogoCurso->replicate();
            $novoCurso->grupo_educacional_id = $request->grupo_educacional_id;
            $novoCurso->codigo = $request->novo_codigo;
            $novoCurso->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Curso duplicado com sucesso!',
                'data' => $novoCurso->load(['grupoEducacional', 'areaConhecimento'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao duplicar curso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NOVO: Sincronizar informações do catálogo com todos os cursos vinculados
     */
    public function sincronizarComCursos(CatalogoCurso $catalogoCurso): JsonResponse
    {
        $totalCursos = $catalogoCurso->cursos()->count();
        
        if ($totalCursos === 0) {
            return response()->json([
                'message' => 'Este curso do catálogo não possui cursos vinculados para sincronizar.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Atualiza todos os cursos vinculados
            $catalogoCurso->cursos()->update([
                'nome' => $catalogoCurso->nome,
                'nivel' => $catalogoCurso->nivel,
                'grau' => $catalogoCurso->grau,
                'modalidade' => $catalogoCurso->modalidade,
                'duracao_padrao_semestres' => $catalogoCurso->duracao_padrao_semestres,
                'prazo_maximo_semestres' => $catalogoCurso->prazo_maximo_semestres,
                'carga_horaria_total' => $catalogoCurso->carga_horaria_total,
                'updated_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => "Sincronização concluída! {$totalCursos} curso(s) atualizado(s).",
                'total_sincronizados' => $totalCursos
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao sincronizar cursos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NOVO: Estatísticas do catálogo
     */
    public function estatisticas(Request $request): JsonResponse
    {
        $grupoId = $request->get('grupo_educacional_id');

        $query = CatalogoCurso::query();

        if ($grupoId) {
            $query->where('grupo_educacional_id', $grupoId);
        }

        $stats = [
            'total_cursos' => $query->count(),
            'ativos' => (clone $query)->where('status', 'ativo')->count(),
            'inativos' => (clone $query)->where('status', 'inativo')->count(),
            'por_nivel' => (clone $query)->select('nivel', DB::raw('count(*) as total'))
                ->groupBy('nivel')
                ->get()
                ->pluck('total', 'nivel'),
            'por_modalidade' => (clone $query)->select('modalidade', DB::raw('count(*) as total'))
                ->groupBy('modalidade')
                ->get()
                ->pluck('total', 'modalidade'),
            'por_grau' => (clone $query)->select('grau', DB::raw('count(*) as total'))
                ->whereNotNull('grau')
                ->groupBy('grau')
                ->get()
                ->pluck('total', 'grau'),
            'sem_uso' => (clone $query)->has('cursos', '=', 0)->count(),
            'em_uso' => (clone $query)->has('cursos', '>', 0)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * ✅ NOVO: Listar instituições que usam um curso do catálogo
     */
    public function instituicoesVinculadas(CatalogoCurso $catalogoCurso): JsonResponse
    {
        $cursos = $catalogoCurso->cursos()
            ->with([
                'instituicao:id,nome_fantasia,sigla',
                'campus:id,nome,instituicao_id'
            ])
            ->get()
            ->groupBy('instituicao_id')
            ->map(function($cursos, $instituicaoId) {
                $primeiraIes = $cursos->first()->instituicao;
                return [
                    'instituicao_id' => $instituicaoId,
                    'nome_fantasia' => $primeiraIes->nome_fantasia,
                    'sigla' => $primeiraIes->sigla,
                    'total_cursos' => $cursos->count(),
                    'cursos' => $cursos->map(fn($c) => [
                        'id' => $c->id,
                        'codigo_ies' => $c->codigo_ies,
                        'campus' => $c->campus ? $c->campus->nome : 'Todos os campi',
                        'status' => $c->status
                    ])
                ];
            })
            ->values();

        return response()->json([
            'total_instituicoes' => $cursos->count(),
            'instituicoes' => $cursos
        ]);
    }
}
