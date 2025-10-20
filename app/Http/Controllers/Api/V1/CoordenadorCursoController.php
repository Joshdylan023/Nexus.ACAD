<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CoordenadorCurso;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CoordenadorCursoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = CoordenadorCurso::with([
                'curso.instituicao',
                'curso.campus',
                'colaborador.usuario',
                'createdBy',
                'updatedBy'
            ]);

            // ✅ FILTRO POR HIERARQUIA: Instituição
            if ($request->filled('instituicao_id')) {
                $query->whereHas('curso', function($q) use ($request) {
                    $q->where('instituicao_id', $request->instituicao_id);
                });
            }

            // ✅ FILTRO POR HIERARQUIA: Campus
            if ($request->filled('campus_id')) {
                $query->whereHas('curso', function($q) use ($request) {
                    $q->where('campus_id', $request->campus_id);
                });
            }

            // Filtro por curso
            if ($request->filled('curso_id')) {
                $query->where('curso_id', $request->curso_id);
            }

            // Filtro por colaborador
            if ($request->filled('colaborador_id')) {
                $query->where('colaborador_id', $request->colaborador_id);
            }

            // Filtro por tipo
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Filtro por status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apenas coordenadores ativos
            if ($request->boolean('apenas_ativos')) {
                $query->ativos();
            }

            // Busca
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('colaborador.usuario', function($q2) use ($search) {
                        $q2->where('name', 'ilike', "%{$search}%");
                    })->orWhereHas('curso', function($q2) use ($search) {
                        $q2->where('nome', 'ilike', "%{$search}%");
                    });
                });
            }

            $sortField = $request->get('sort_field', 'data_inicio');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            $perPage = $request->get('per_page', 15);
            
            if ($request->boolean('all')) {
                return response()->json($query->get());
            }

            return response()->json($query->paginate($perPage));

        } catch (\Exception $e) {
            Log::error('Erro ao listar coordenadores de curso: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao listar coordenadores',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $coordenador = CoordenadorCurso::with([
                'curso',
                'colaborador.usuario',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return response()->json($coordenador);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Coordenador não encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'curso_id' => 'required|exists:cursos,id',
                'colaborador_id' => 'required|exists:colaboradores,id',
                'tipo' => 'required|in:Titular,Adjunto',
                'data_inicio' => 'required|date',
                'data_fim' => 'nullable|date|after:data_inicio',
                'portaria' => 'nullable|string|max:255',
                'status' => 'required|in:Ativo,Inativo',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ VALIDAÇÃO: Apenas 1 coordenador titular ativo por curso
            if ($request->tipo === 'Titular' && $request->status === 'Ativo') {
                $existeTitular = CoordenadorCurso::where('curso_id', $request->curso_id)
                    ->where('tipo', 'Titular')
                    ->ativos()
                    ->where('id', '!=', $request->id ?? 0)
                    ->exists();

                if ($existeTitular) {
                    return response()->json([
                        'message' => 'Já existe um coordenador titular ativo para este curso'
                    ], 422);
                }
            }

            DB::beginTransaction();
            try {
                $data = $request->all();
                $data['created_by'] = auth()->id();
                $data['updated_by'] = auth()->id();

                $coordenador = CoordenadorCurso::create($data);
                // ✅ Observer cuida da sincronização automaticamente

                DB::commit();

                Log::info('Coordenador de curso criado', [
                    'coordenador_id' => $coordenador->id,
                    'user_id' => auth()->id()
                ]);

                return response()->json([
                    'message' => 'Coordenador criado com sucesso',
                    'data' => $coordenador->load(['curso', 'colaborador.usuario'])
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao criar coordenador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao criar coordenador',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $coordenador = CoordenadorCurso::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'curso_id' => 'required|exists:cursos,id',
                'colaborador_id' => 'required|exists:colaboradores,id',
                'tipo' => 'required|in:Titular,Adjunto',
                'data_inicio' => 'required|date',
                'data_fim' => 'nullable|date|after:data_inicio',
                'portaria' => 'nullable|string|max:255',
                'status' => 'required|in:Ativo,Inativo',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ VALIDAÇÃO: Apenas 1 coordenador titular ativo por curso
            if ($request->tipo === 'Titular' && $request->status === 'Ativo') {
                $existeTitular = CoordenadorCurso::where('curso_id', $request->curso_id)
                    ->where('tipo', 'Titular')
                    ->ativos()
                    ->where('id', '!=', $id)
                    ->exists();

                if ($existeTitular) {
                    return response()->json([
                        'message' => 'Já existe um coordenador titular ativo para este curso'
                    ], 422);
                }
            }

            DB::beginTransaction();
            try {
                $data = $request->all();
                $data['updated_by'] = auth()->id();
                unset($data['created_by']);

                $coordenador->update($data);
                // ✅ Observer cuida da sincronização automaticamente

                DB::commit();

                return response()->json([
                    'message' => 'Coordenador atualizado com sucesso',
                    'data' => $coordenador->load(['curso', 'colaborador.usuario'])
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar coordenador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar coordenador',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $coordenador = CoordenadorCurso::findOrFail($id);

            DB::beginTransaction();
            try {
                $coordenador->delete();
                // ✅ Observer cuida da sincronização automaticamente
                
                DB::commit();

                return response()->json(['message' => 'Coordenador excluído com sucesso']);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao excluir coordenador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir coordenador',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * ✅ SINCRONIZAR TODOS OS COORDENADORES TITULARES COM CURSOS
     */
    public function sincronizarTitulares()
    {
        try {
            DB::beginTransaction();
            
            // 1. Obter todos os coordenadores titulares ATIVOS e mapeá-los por curso_id.
            $coordenadoresAtivosMap = CoordenadorCurso::titularesAtivos()
                ->get()
                ->keyBy('curso_id');

            $cursosComCoordenadorAtivoIds = $coordenadoresAtivosMap->keys()->all();

            // 2. Limpar coordenador_id de cursos que não têm mais um titular ativo.
            $cursosLimpados = Curso::whereNotNull('coordenador_id')
                ->whereNotIn('id', $cursosComCoordenadorAtivoIds)
                ->update(['coordenador_id' => null]);

            $sincronizados = 0;
            
            // 3. Atualizar ou definir o coordenador_id para os cursos com titulares ativos.
            foreach ($coordenadoresAtivosMap as $cursoId => $coordenador) {
                $updated = Curso::where('id', $cursoId)
                    ->update(['coordenador_id' => $coordenador->colaborador_id]);

                if ($updated) {
                    $sincronizados++;
                }
            }

            DB::commit();

            Log::info('Sincronização de coordenadores titulares concluída.', [
                'cursos_atualizados' => $sincronizados,
                'cursos_sem_coordenador' => $cursosLimpados,
                'total_coordenadores_ativos' => $coordenadoresAtivosMap->count(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Sincronização concluída!',
                'cursos_atualizados' => $sincronizados,
                'cursos_sem_coordenador' => $cursosLimpados,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao sincronizar coordenadores titulares: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'Ocorreu um erro inesperado durante a sincronização.',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor.'
            ], 500);
        }
    }

    public function porCurso($cursoId)
    {
        try {
            $coordenadores = CoordenadorCurso::with(['colaborador.usuario'])
                ->where('curso_id', $cursoId)
                ->ativos()
                ->orderBy('tipo', 'asc')
                ->get();

            return response()->json($coordenadores);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar coordenadores por curso: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar coordenadores',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function dashboard()
    {
        try {
            $total = CoordenadorCurso::count();
            $ativos = CoordenadorCurso::where('status', 'Ativo')->count();
            $titulares = CoordenadorCurso::where('tipo', 'Titular')->where('status', 'Ativo')->count();
            $adjuntos = CoordenadorCurso::where('tipo', 'Adjunto')->where('status', 'Ativo')->count();
            
            $cursosSemCoordenador = Curso::whereNull('coordenador_id')
                ->where('status', 'Ativo')
                ->count();

            return response()->json([
                'total' => $total,
                'ativos' => $ativos,
                'titulares' => $titulares,
                'adjuntos' => $adjuntos,
                'cursos_sem_coordenador' => $cursosSemCoordenador
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dashboard: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    public function historicoCurso($cursoId)
    {
        try {
            $historico = CoordenadorCurso::where('curso_id', $cursoId)
                ->with(['colaborador.usuario'])
                ->orderBy('data_inicio', 'desc')
                ->get();

            return response()->json($historico);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar histórico: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar histórico',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}
