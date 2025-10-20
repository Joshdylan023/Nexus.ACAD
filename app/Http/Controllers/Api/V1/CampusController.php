<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Setor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampusController extends Controller
{
    /**
     * Exibe uma lista de todos os Campi.
     */
    public function index(Request $request): JsonResponse
    {
        // Inicia a query com relacionamentos
        $query = Campus::with(['instituicao', 'gerenteUnidade']);

        // ⭐ FILTRO POR INSTITUIÇÃO
        if ($request->filled('instituicao_id')) {
            $query->where('instituicao_id', $request->instituicao_id);
        }

        // ⭐ FILTRO POR STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ⭐ BUSCA POR NOME
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'ilike', '%' . $request->search . '%')
                  ->orWhere('endereco_completo', 'ilike', '%' . $request->search . '%');
            });
        }

        // Ordenação
        $query->orderBy('nome', 'asc');

        // ⭐ RETORNAR TODOS OU PAGINADOS
        if ($request->has('all') && $request->all == 'true') {
            $campi = $query->get();
            return response()->json($campi);
        }

        // Paginação
        $campi = $query->paginate($request->per_page ?? 15);
        return response()->json($campi);
    }

    /**
     * Armazena um novo Campus.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'nome' => 'required|string|max:255',
            'endereco_completo' => 'required|string',
            'gerente_unidade_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo'])],
        ]);
        $campus = Campus::create($validatedData);

        return response()->json(['message' => 'Campus criado com sucesso!', 'data' => $campus->load(['instituicao', 'gerenteUnidade'])], 201);
    }

    /**
     * Exibe os detalhes de um Campus específico.
     */
    public function show(Campus $campus): JsonResponse
    {
        return response()->json($campus->load(['instituicao', 'gerenteUnidade']));
    }

    /**
     * Atualiza os dados de um Campus existente.
     */
    public function update(Request $request, Campus $campus): JsonResponse
    {
        $validatedData = $request->validate([
            'instituicao_id' => 'required|exists:instituicoes,id',
            'nome' => 'required|string|max:255',
            'endereco_completo' => 'required|string',
            'gerente_unidade_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(['Ativo', 'Inativo'])],
        ]);
        $campus->update($validatedData);

        return response()->json(['message' => 'Campus atualizado com sucesso!', 'data' => $campus->load(['instituicao', 'gerenteUnidade'])]);
    }

    /**
     * Remove um Campus.
     */
    public function destroy(Campus $campus): JsonResponse
    {
        $campus->delete();

        return response()->json(null, 204);
    }

    /**
     * Listar setores de um campus
     */
    public function setores($id): JsonResponse
    {
        try {
            $setores = Setor::where('entidade_tipo', 'Campus')
                ->where('entidade_id', $id)
                ->select('id', 'nome', 'sigla')
                ->orderBy('nome')
                ->get();
            
            return response()->json($setores);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
