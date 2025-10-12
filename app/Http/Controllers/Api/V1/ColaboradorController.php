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
use Illuminate\Database\Eloquent\Relations\Relation;

class ColaboradorController extends Controller
{
    public function index(): JsonResponse
    {
        $colaboradores = Colaborador::with([
            'usuario', 
            'unidadeLotacao', 
            'unidadeOrganizacional'
        ])->get();
        return response()->json($colaboradores);
    }

    public function show($id): JsonResponse
    {
        $colaborador = Colaborador::with(['usuario', 'unidadeOrganizacional', 'unidadeLotacao', 'setor'])->find($id);

        if (!$colaborador) {
            return response()->json(['message' => 'Colaborador não encontrado'], 404);
        }

        return response()->json($colaborador);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            // User data
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|unique:users,cpf',
            'email' => 'required|string|email|unique:users,email',
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
            
            // Polymorphic relations data
            'unidade_organizacional_id' => 'required|integer',
            'unidade_organizacional_type' => ['required', 'string', Rule::in(array_keys(Relation::getMorphMap()))],
            'unidade_lotacao_id' => 'required|integer',
            'unidade_lotacao_type' => ['required', 'string', Rule::in(array_keys(Relation::getMorphMap()))],
            'setor_vinculo_id' => 'required|exists:setor_vinculos,id',

            // Photos
            'foto_registro_rh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $colaborador = DB::transaction(function () use ($request, $validatedData) {
            $user = User::create([
                'name' => $validatedData['name'],
                'cpf' => $validatedData['cpf'],
                'email' => $validatedData['email'],
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
                'password' => Hash::make($validatedData['password']), // Assuming user has password
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
                'unidade_organizacional_id' => $validatedData['unidade_organizacional_id'],
                'unidade_organizacional_type' => $validatedData['unidade_organizacional_type'],
                'unidade_lotacao_id' => $validatedData['unidade_lotacao_id'],
                'unidade_lotacao_type' => $validatedData['unidade_lotacao_type'],
                'setor_vinculo_id' => $validatedData['setor_vinculo_id'],
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
            // User data
            'name' => 'required|string|max:255',
            'cpf' => ['required', 'string', Rule::unique('users')->ignore($colaborador->user_id)],
            'email' => ['required', 'string', 'email', Rule::unique('users')->ignore($colaborador->user_id)],
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
            'matricula_funcional' => ['required', 'string', Rule::unique('colaboradores')->ignore($colaborador->id)],
            'email_funcional' => ['required', 'string', 'email', Rule::unique('colaboradores')->ignore($colaborador->id)],
            'password' => 'nullable|string|min:8', // Password is optional on update
            'cargo' => 'required|string|max:255',
            'data_admissao' => 'required|date',
            'status' => 'required|in:Ativo,Afastado,Desligado',
            'data_desligamento' => 'nullable|date|required_if:status,Desligado',
            'is_gestor' => 'required|boolean',
            'gestor_imediato_id' => 'nullable|exists:colaboradores,id',
            
            // Polymorphic relations data
            'unidade_organizacional_id' => 'required|integer',
            'unidade_organizacional_type' => ['required', 'string', Rule::in(array_keys(Relation::getMorphMap()))],
            'unidade_lotacao_id' => 'required|integer',
            'unidade_lotacao_type' => ['required', 'string', Rule::in(array_keys(Relation::getMorphMap()))],
            'setor_vinculo_id' => 'required|exists:setor_vinculos,id',

            // Photos
            'foto_registro_rh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::transaction(function () use ($request, $validatedData, $colaborador) {
            $user = $colaborador->usuario;
            $user->update([
                'name' => $validatedData['name'],
                'cpf' => $validatedData['cpf'],
                'email' => $validatedData['email'],
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
                'cargo' => $validatedData['cargo'],
                'data_admissao' => $validatedData['data_admissao'],
                'status' => $validatedData['status'],
                'data_desligamento' => $validatedData['data_desligamento'] ?? null,
                'is_gestor' => $validatedData['is_gestor'],
                'gestor_imediato_id' => $validatedData['gestor_imediato_id'] ?? null,
                'unidade_organizacional_id' => $validatedData['unidade_organizacional_id'],
                'unidade_organizacional_type' => $validatedData['unidade_organizacional_type'],
                'unidade_lotacao_id' => $validatedData['unidade_lotacao_id'],
                'unidade_lotacao_type' => $validatedData['unidade_lotacao_type'],
                'setor_vinculo_id' => $validatedData['setor_vinculo_id'],
            ];

            if (!empty($validatedData['password'])) {
                $colaboradorData['password'] = Hash::make($validatedData['password']);
            }

            if ($request->hasFile('foto_registro_rh')) {
                $path = $request->file('foto_registro_rh')->store('fotos_colaboradores', 'public');
                $colaboradorData['foto_registro_rh'] = $path;
            }

            $colaborador->update($colaboradorData);
        });

        return response()->json(['message' => 'Colaborador atualizado com sucesso!', 'data' => $colaborador->load('usuario', 'unidadeOrganizacional', 'unidadeLotacao')]);
    }

    public function destroy(Colaborador $colaborador): JsonResponse
    {
        DB::transaction(function () use ($colaborador) {
            $colaborador->delete();
        });

        return response()->json(['message' => 'Colaborador excluído com sucesso!']);
    }
}