<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class ColaboradorController extends Controller
{
    public function index(Request $request): JsonResponse
{
    try {
        $query = Colaborador::with([
            'usuario', 
            'unidadeLotacao', 
            'unidadeOrganizacional',
            'setorVinculo.setor',
            'gestorImediato.usuario'
        ]);

        // ⭐ FILTRO POR GESTOR IMEDIATO
        if ($request->has('gestor_imediato_id')) {
            $gestorId = $request->gestor_imediato_id;
            \Log::info("Filtrando por gestor_imediato_id: {$gestorId}");
            $query->where('gestor_imediato_id', $gestorId);
        }

        // BUSCA POR NOME OU MATRÍCULA
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricula_funcional', 'ILIKE', "%{$search}%")
                  ->orWhereHas('usuario', function($q2) use ($search) {
                      $q2->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // FILTRO POR STATUS
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');

        // ⭐ SE TEM FILTRO DE GESTOR, RETORNA ARRAY SIMPLES (SEM PAGINAÇÃO)
        if ($request->has('gestor_imediato_id')) {
            $colaboradores = $query->get();
            \Log::info("Encontrados {$colaboradores->count()} colaboradores");
            return response()->json($colaboradores);
        }

        // SENÃO, RETORNA PAGINADO
        $perPage = $request->get('per_page', 15);
        $colaboradores = $query->paginate($perPage);
        
        return response()->json($colaboradores);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao listar colaboradores: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return response()->json([
            'message' => 'Erro ao listar colaboradores', 
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function show($id): JsonResponse
    {
        try {
            $colaborador = Colaborador::with([
                'usuario',
                'unidadeOrganizacional',
                'unidadeLotacao',
                'setorVinculo.setor',
                'gestorImediato.usuario',
            ])->find($id);

            if (!$colaborador) {
                return response()->json(['message' => 'Colaborador não encontrado'], 404);
            }

            return response()->json($colaborador);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar colaborador: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao buscar colaborador', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            // User data
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|unique:users,cpf',
            'email' => 'required|string|email|unique:users,email',
            'email_pessoal' => 'nullable|string|email|unique:users,email_pessoal',
            'nome_social' => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'rg' => 'nullable|string|max:20',
            'rg_orgao_expedidor' => 'nullable|string|max:255',
            'rg_data_expedicao' => 'nullable|date',
            'nacionalidade' => 'nullable|string|max:255',
            'naturalidade_cidade' => 'nullable|string|max:255',
            'naturalidade_uf' => 'nullable|string|max:2',
            'telefone_principal' => 'nullable|string|max:20',
            'telefone_secundario' => 'nullable|string|max:20',
            'nome_pai' => 'nullable|string|max:255',
            'nome_mae' => 'nullable|string|max:255',
            'ensino_medio_instituicao' => 'nullable|string|max:255',
            'ensino_medio_ano_conclusao' => 'nullable|integer',
            'endereco_completo' => 'nullable|string',

            // Colaborador data
            'matricula_funcional' => 'required|string|unique:colaboradores,matricula_funcional',
            'email_funcional' => 'required|string|email|unique:colaboradores,email_funcional',
            'password' => 'required|string|min:8',
            'cargo' => 'required|string|max:255',
            'data_admissao' => 'required|date',
            'status' => 'required|in:Ativo,Afastado,Desligado',
            'data_desligamento' => 'nullable|date|required_if:status,Desligado',
            'is_gestor' => 'required|boolean',
            'gestor_imediato_id' => 'nullable|exists:colaboradores,id',
            
            'unidade_organizacional_id' => 'nullable|integer',
            'unidade_organizacional_type' => ['nullable', 'string', Rule::in(['grupo_educacional', 'mantenedora', 'instituicao', 'campus', 'setor'])],
            'unidade_lotacao_id' => 'nullable|integer',
            'unidade_lotacao_type' => ['nullable', 'string', Rule::in(['grupo_educacional', 'mantenedora', 'instituicao'])],
            'setor_vinculo_id' => 'nullable|exists:setor_vinculos,id',

            'foto_registro_rh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $colaborador = DB::transaction(function () use ($request, $validatedData) {
            $user = User::create([
                'name' => $validatedData['name'],
                'cpf' => $validatedData['cpf'],
                'email' => $validatedData['email'],
                'email_pessoal' => $validatedData['email_pessoal'] ?? null,
                'nome_social' => $validatedData['nome_social'] ?? null,
                'data_nascimento' => $validatedData['data_nascimento'] ?? null,
                'rg' => $validatedData['rg'] ?? null,
                'rg_orgao_expedidor' => $validatedData['rg_orgao_expedidor'] ?? null,
                'rg_data_expedicao' => $validatedData['rg_data_expedicao'] ?? null,
                'nacionalidade' => $validatedData['nacionalidade'] ?? null,
                'naturalidade_cidade' => $validatedData['naturalidade_cidade'] ?? null,
                'naturalidade_uf' => $validatedData['naturalidade_uf'] ?? null,
                'telefone_principal' => $validatedData['telefone_principal'] ?? null,
                'telefone_secundario' => $validatedData['telefone_secundario'] ?? null,
                'nome_pai' => $validatedData['nome_pai'] ?? null,
                'nome_mae' => $validatedData['nome_mae'] ?? null,
                'ensino_medio_instituicao' => $validatedData['ensino_medio_instituicao'] ?? null,
                'ensino_medio_ano_conclusao' => $validatedData['ensino_medio_ano_conclusao'] ?? null,
                'endereco_completo' => $validatedData['endereco_completo'] ?? null,
            ]);

            $colaboradorData = [
                'matricula_funcional' => $validatedData['matricula_funcional'],
                'email_funcional' => $validatedData['email_funcional'],
                'password' => Hash::make($validatedData['password']),
                'cargo' => $validatedData['cargo'],
                'data_admissao' => $validatedData['data_admissao'],
                'status' => $validatedData['status'],
                'data_desligamento' => $validatedData['data_desligamento'] ?? null,
                'is_gestor' => $validatedData['is_gestor'],
                'gestor_imediato_id' => $validatedData['gestor_imediato_id'] ?? null,
                'unidade_organizacional_id' => $validatedData['unidade_organizacional_id'] ?? null,
                'unidade_organizacional_type' => $validatedData['unidade_organizacional_type'] ?? null,
                'unidade_lotacao_id' => $validatedData['unidade_lotacao_id'] ?? null,
                'unidade_lotacao_type' => $validatedData['unidade_lotacao_type'] ?? null,
                'setor_vinculo_id' => $validatedData['setor_vinculo_id'] ?? null,
            ];

            if ($request->hasFile('foto_registro_rh')) {
                $path = $request->file('foto_registro_rh')->store('fotos_colaboradores', 'public');
                $colaboradorData['foto_registro_rh'] = $path;
            }

            return $user->colaborador()->create($colaboradorData);
        });

        return response()->json(['message' => 'Colaborador criado com sucesso!', 'data' => $colaborador->load('usuario')], 201);
    }

    public function update(Request $request, Colaborador $colaborador): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'cpf' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($colaborador->user_id)],
            'email' => ['sometimes', 'required', 'string', 'email', Rule::unique('users')->ignore($colaborador->user_id)],
            'email_pessoal' => ['sometimes', 'nullable', 'string', 'email', Rule::unique('users', 'email_pessoal')->ignore($colaborador->user_id)],
            'nome_social' => 'sometimes|nullable|string|max:255',
            'data_nascimento' => 'sometimes|nullable|date',
            'rg' => 'sometimes|nullable|string|max:20',
            'rg_orgao_expedidor' => 'sometimes|nullable|string|max:255',
            'rg_data_expedicao' => 'sometimes|nullable|date',
            'nacionalidade' => 'sometimes|nullable|string|max:255',
            'naturalidade_cidade' => 'sometimes|nullable|string|max:255',
            'naturalidade_uf' => 'sometimes|nullable|string|max:2',
            'telefone_principal' => 'sometimes|nullable|string|max:20',
            'telefone_secundario' => 'sometimes|nullable|string|max:20',
            'nome_pai' => 'sometimes|nullable|string|max:255',
            'nome_mae' => 'sometimes|nullable|string|max:255',
            'ensino_medio_instituicao' => 'sometimes|nullable|string|max:255',
            'ensino_medio_ano_conclusao' => 'sometimes|nullable|integer',
            'endereco_completo' => 'sometimes|nullable|string',

            'matricula_funcional' => ['sometimes', 'required', 'string', Rule::unique('colaboradores')->ignore($colaborador->id)],
            'email_funcional' => ['sometimes', 'required', 'string', 'email', Rule::unique('colaboradores')->ignore($colaborador->id)],
            'password' => 'sometimes|nullable|string|min:8',
            'cargo' => 'sometimes|required|string|max:255',
            'data_admissao' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Ativo,Afastado,Desligado',
            'data_desligamento' => 'sometimes|nullable|date|required_if:status,Desligado',
            'is_gestor' => 'sometimes|required|boolean',
            'gestor_imediato_id' => 'sometimes|nullable|exists:colaboradores,id',
            
            'unidade_organizacional_id' => 'sometimes|nullable|integer',
            'unidade_organizacional_type' => ['sometimes', 'nullable', 'string', Rule::in(['grupo_educacional', 'mantenedora', 'instituicao', 'campus', 'setor'])],
            'unidade_lotacao_id' => 'sometimes|nullable|integer',
            'unidade_lotacao_type' => ['sometimes', 'nullable', 'string', Rule::in(['grupo_educacional', 'mantenedora', 'instituicao'])],
            'setor_vinculo_id' => 'sometimes|nullable|exists:setor_vinculos,id',

            'foto_registro_rh' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::transaction(function () use ($request, $validatedData, $colaborador) {
            $user = $colaborador->usuario;

            $userFields = [
                'name', 'cpf', 'email', 'email_pessoal', 'nome_social', 'data_nascimento', 'rg', 'rg_orgao_expedidor', 
                'rg_data_expedicao', 'nacionalidade', 'naturalidade_cidade', 'naturalidade_uf', 
                'telefone_principal', 'telefone_secundario', 'nome_pai', 'nome_mae', 'ensino_medio_instituicao', 
                'ensino_medio_ano_conclusao', 'endereco_completo'
            ];
            $userData = array_intersect_key($validatedData, array_flip($userFields));

            if (!empty($userData)) {
                $user->update($userData);
            }

            $colaboradorFields = [
                'matricula_funcional', 'email_funcional', 'cargo', 'data_admissao', 'status', 'data_desligamento', 
                'is_gestor', 'gestor_imediato_id', 'unidade_organizacional_id', 'unidade_organizacional_type', 
                'unidade_lotacao_id', 'unidade_lotacao_type', 'setor_vinculo_id'
            ];
            $colaboradorData = array_intersect_key($validatedData, array_flip($colaboradorFields));

            if (!empty($validatedData['password'])) {
                $colaboradorData['password'] = Hash::make($validatedData['password']);
            }

            if ($request->hasFile('foto_registro_rh')) {
                $path = $request->file('foto_registro_rh')->store('fotos_colaboradores', 'public');
                $colaboradorData['foto_registro_rh'] = $path;
            }

            if (!empty($colaboradorData)) {
                $colaborador->update($colaboradorData);
            }
        });

        return response()->json(['message' => 'Colaborador atualizado com sucesso!', 'data' => $colaborador->load('usuario', 'unidadeOrganizacional', 'unidadeLotacao', 'setorVinculo.setor')]);
    }

    public function destroy(Colaborador $colaborador): JsonResponse
    {
        DB::transaction(function () use ($colaborador) {
            $colaborador->delete();
        });

        return response()->json(['message' => 'Colaborador excluído com sucesso!']);
    }

    /**
 * Retorna a equipe contextual (sempre mostra o gestor quando existe)
 */
public function minhaEquipe(Request $request): JsonResponse
{
    $user = Auth::user();
    
    if (!$user->colaborador) {
        return response()->json([
            'message' => 'Usuário não é colaborador'
        ], 403);
    }

    $colaborador = $user->colaborador;
    $isGestor = $colaborador->is_gestor;
    $meuGestor = null;
    $subordinados = collect([]);
    $colegas = collect([]);

    // ⭐ SEMPRE busca o gestor (se existir)
    if ($colaborador->gestor_imediato_id) {
        $gestorModel = Colaborador::with([
            'usuario',
            'setorVinculo.setor',
            'unidadeLotacao'
        ])
        ->where('id', $colaborador->gestor_imediato_id)
        ->first();

        if ($gestorModel) {
            $meuGestor = $this->formatarColaborador($gestorModel, true);
        }
    }

    if ($isGestor) {
        // ⭐ GESTOR: Retorna subordinados diretos
        $subordinados = Colaborador::with([
            'usuario',
            'setorVinculo.setor',
            'unidadeLotacao'
        ])
        ->where('gestor_imediato_id', $colaborador->id)
        ->where('status', 'Ativo')
        ->get()
        ->map(function($c) {
            return $this->formatarColaborador($c);
        });
    } else {
        // ⭐ COLABORADOR: Retorna colegas (mesma equipe)
        if ($colaborador->gestor_imediato_id) {
            $colegas = Colaborador::with([
                'usuario',
                'setorVinculo.setor',
                'unidadeLotacao'
            ])
            ->where('gestor_imediato_id', $colaborador->gestor_imediato_id)
            ->where('id', '!=', $colaborador->id) // Exclui ele mesmo
            ->where('status', 'Ativo')
            ->get()
            ->map(function($c) {
                return $this->formatarColaborador($c);
            });
        }
    }

    return response()->json([
        'is_gestor' => $isGestor,
        'meu_gestor' => $meuGestor,
        'subordinados' => $subordinados,
        'colegas' => $colegas,
        'total_subordinados' => $subordinados->count(),
        'total_colegas' => $colegas->count(),
    ]);
}

/**
 * Método auxiliar para formatar dados do colaborador
 */
private function formatarColaborador($colaborador, $isGestorFlag = null)
{
    return [
        'id' => $colaborador->id,
        'user_id' => $colaborador->user_id,
        'nome' => $colaborador->usuario->name,
        'email' => $colaborador->usuario->email,
        'email_funcional' => $colaborador->email_funcional,
        'foto' => $colaborador->foto_registro_rh ? "/storage/{$colaborador->foto_registro_rh}" : null,
        'cargo' => $colaborador->cargo,
        'matricula' => $colaborador->matricula_funcional,
        'setor' => $colaborador->setorVinculo?->setor?->nome,
        'unidade' => $colaborador->unidadeLotacao?->nome_fantasia ?? $colaborador->unidadeLotacao?->razao_social,
        'data_admissao' => $colaborador->data_admissao ? Carbon::parse($colaborador->data_admissao)->format('d/m/Y') : null,
        'telefone' => $colaborador->usuario->telefone_principal,
        'status' => $colaborador->status,
        'is_gestor' => $isGestorFlag ?? $colaborador->is_gestor,
    ];
}

/**
 * Retorna estatísticas da equipe
 */
public function minhaEquipeStats(Request $request): JsonResponse
{
    $user = Auth::user();
    
    if (!$user->colaborador) {
        return response()->json([
            'message' => 'Usuário não é colaborador'
        ], 403);
    }

    $totalEquipe = Colaborador::where('gestor_imediato_id', $user->colaborador->id)
        ->where('status', 'Ativo')
        ->count();

    $totalGestores = Colaborador::where('gestor_imediato_id', $user->colaborador->id)
        ->where('status', 'Ativo')
        ->where('is_gestor', true)
        ->count();

    $totalSubordinadosIndiretos = Colaborador::whereHas('gestorImediato', function($q) use ($user) {
        $q->where('gestor_imediato_id', $user->colaborador->id);
    })
    ->where('status', 'Ativo')
    ->count();

    return response()->json([
        'total_diretos' => $totalEquipe,
        'total_gestores' => $totalGestores,
        'total_indiretos' => $totalSubordinadosIndiretos,
        'total_geral' => $totalEquipe + $totalSubordinadosIndiretos
    ]);
    
}

/**
 * Exportar equipe para Excel
 */
public function exportarEquipeExcel(Request $request)
{
    $user = Auth::user();
    
    if (!$user->colaborador) {
        return response()->json(['message' => 'Usuário não é colaborador'], 403);
    }

    $colaborador = $user->colaborador;
    $isGestor = $colaborador->is_gestor;

    // Buscar dados
    if ($isGestor) {
        $dados = Colaborador::with(['usuario', 'setorVinculo.setor', 'unidadeLotacao'])
            ->where('gestor_imediato_id', $colaborador->id)
            ->where('status', 'Ativo')
            ->get()
            ->map(function($c) {
                return $this->formatarColaborador($c);
            });
        $titulo = 'Meus Subordinados Diretos';
    } else {
        $dados = Colaborador::with(['usuario', 'setorVinculo.setor', 'unidadeLotacao'])
            ->where('gestor_imediato_id', $colaborador->gestor_imediato_id)
            ->where('id', '!=', $colaborador->id)
            ->where('status', 'Ativo')
            ->get()
            ->map(function($c) {
                return $this->formatarColaborador($c);
            });
        $titulo = 'Meus Colegas de Equipe';
    }

    $fileName = 'minha-equipe-' . now()->format('Y-m-d') . '.xlsx';

    return \Excel::download(new \App\Exports\MinhaEquipeExport($dados, $titulo), $fileName);
}

/**
 * Exportar equipe para PDF
 */
public function exportarEquipePDF(Request $request)
{
    $user = Auth::user();
    
    if (!$user->colaborador) {
        return response()->json(['message' => 'Usuário não é colaborador'], 403);
    }

    $colaborador = $user->colaborador;
    $isGestor = $colaborador->is_gestor;
    $gestor = null;

    // Buscar gestor se existir
    if ($colaborador->gestor_imediato_id) {
        $gestorModel = Colaborador::with(['usuario', 'setorVinculo.setor'])
            ->where('id', $colaborador->gestor_imediato_id)
            ->first();
        
        if ($gestorModel) {
            $gestor = $this->formatarColaborador($gestorModel, true);
        }
    }

    // Buscar dados
    if ($isGestor) {
        $dados = Colaborador::with(['usuario', 'setorVinculo.setor', 'unidadeLotacao'])
            ->where('gestor_imediato_id', $colaborador->id)
            ->where('status', 'Ativo')
            ->get()
            ->map(function($c) {
                return $this->formatarColaborador($c);
            });
        $titulo = 'Meus Subordinados Diretos';
    } else {
        $dados = Colaborador::with(['usuario', 'setorVinculo.setor', 'unidadeLotacao'])
            ->where('gestor_imediato_id', $colaborador->gestor_imediato_id)
            ->where('id', '!=', $colaborador->id)
            ->where('status', 'Ativo')
            ->get()
            ->map(function($c) {
                return $this->formatarColaborador($c);
            });
        $titulo = 'Meus Colegas de Equipe';
    }

    $pdf = \PDF::loadView('exports.minha-equipe-pdf', [
        'titulo' => $titulo,
        'colaborador' => $user->name,
        'is_gestor' => $isGestor,
        'gestor' => $gestor,
        'dados' => $dados,
        'data' => now()->format('d/m/Y H:i'),
    ]);

    return $pdf->download('minha-equipe-' . now()->format('Y-m-d') . '.pdf');
}

/**
 * Retorna organograma completo
 */
/**
 * Retorna organograma completo
 */
public function organograma(Request $request): JsonResponse
{
    try {
        // Buscar todos os colaboradores ativos
        $todosColaboradores = Colaborador::with([
            'usuario',
            'setorVinculo.setor',
            'unidadeLotacao'
        ])
        ->where('status', 'Ativo')
        ->get();

        // Formatar todos os colaboradores
        $colaboradoresFormatados = $todosColaboradores->map(function($c) {
            return [
                'id' => $c->id,
                'user_id' => $c->user_id,
                'nome' => $c->usuario->name,
                'email' => $c->usuario->email,
                'email_funcional' => $c->email_funcional,
                'foto' => $c->foto_registro_rh ? "/storage/{$c->foto_registro_rh}" : null,
                'cargo' => $c->cargo,
                'matricula' => $c->matricula_funcional,
                'setor' => $c->setorVinculo?->setor?->nome,
                'unidade' => $c->unidadeLotacao?->nome_fantasia ?? $c->unidadeLotacao?->razao_social,
                'data_admissao' => $c->data_admissao ? Carbon::parse($c->data_admissao)->format('d/m/Y') : null,
                'telefone' => $c->usuario->telefone_principal,
                'status' => $c->status,
                'is_gestor' => $c->is_gestor,
                'gestor_imediato_id' => $c->gestor_imediato_id,
            ];
        });

        // Encontrar a raiz (colaborador sem gestor)
        $raiz = $colaboradoresFormatados->firstWhere('gestor_imediato_id', null);

        if (!$raiz) {
            return response()->json([
                'message' => 'Nenhum colaborador raiz encontrado',
                'raiz' => null,
                'colaboradores' => $colaboradoresFormatados
            ]);
        }

        \Log::info('📊 Organograma - Total de colaboradores:', [
            'total' => $colaboradoresFormatados->count(),
            'raiz_id' => $raiz['id'],
            'raiz_nome' => $raiz['nome']
        ]);

        return response()->json([
            'raiz' => $raiz,
            'colaboradores' => $colaboradoresFormatados->toArray()
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar organograma: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return response()->json([
            'message' => 'Erro ao buscar organograma',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
