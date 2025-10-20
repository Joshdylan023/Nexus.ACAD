<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bloco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\ExportaPdfTrait;
use Barryvdh\DomPDF\Facade\Pdf;

class BlocoController extends Controller
{
    use ExportaPdfTrait;

    public function index(Request $request)
    {
        try {
            $query = Bloco::with(['predio.campus.instituicao', 'andares', 'createdBy', 'updatedBy']);

            // ⭐ FILTRO POR PRÉDIO
            if ($request->has('predio_id')) {
                $query->where('predio_id', $request->predio_id);
            }

            // ⭐ NOVO: FILTRO POR CAMPUS (através do prédio)
            if ($request->filled('campus_id')) {
                $query->whereHas('predio', function($q) use ($request) {
                    $q->where('campus_id', $request->campus_id);
                });
            }

            // ⭐ NOVO: FILTRO POR INSTITUIÇÃO (através do prédio e campus)
            if ($request->filled('instituicao_id')) {
                $query->whereHas('predio.campus', function($q) use ($request) {
                    $q->where('instituicao_id', $request->instituicao_id);
                });
            }

            // FILTRO POR STATUS
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // BUSCA POR NOME/CÓDIGO
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
                });
            }

            $sortField = $request->get('sort_field', 'nome');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $perPage = $request->get('per_page', 15);
            
            if ($request->has('all') && $request->boolean('all')) {
                return response()->json($query->get());
            }

            return response()->json($query->paginate($perPage));

        } catch (\Exception $e) {
            Log::error('Erro ao listar blocos: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao listar blocos', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $bloco = Bloco::with([
                'predio.campus.instituicao',
                'andares.espacosFisicos',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return response()->json($bloco);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Bloco não encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'predio_id' => 'required|exists:predios,id',
                'codigo' => 'required|string|max:50',
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'total_andares' => 'nullable|integer|min:0',
                'acessibilidade' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção',
                'fotos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do código por prédio
            $exists = Bloco::where('predio_id', $request->predio_id)
                          ->where('codigo', $request->codigo)
                          ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um bloco com este código neste prédio'], 422);
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $bloco = Bloco::create($data);

            return response()->json([
                'message' => 'Bloco criado com sucesso',
                'data' => $bloco->load('predio')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar bloco: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar bloco', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bloco = Bloco::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'predio_id' => 'required|exists:predios,id',
                'codigo' => 'required|string|max:50',
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'total_andares' => 'nullable|integer|min:0',
                'acessibilidade' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção',
                'fotos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do código por prédio
            $exists = Bloco::where('predio_id', $request->predio_id)
                          ->where('codigo', $request->codigo)
                          ->where('id', '!=', $id)
                          ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um bloco com este código neste prédio'], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            // Remove o campo 'created_by' para evitar que ele seja atualizado
            unset($data['created_by']);

            $bloco->update($data);

            return response()->json([
                'message' => 'Bloco atualizado com sucesso',
                'data' => $bloco->load('predio')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar bloco: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao atualizar bloco', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bloco = Bloco::findOrFail($id);

            if ($bloco->andares()->count() > 0) {
                return response()->json(['message' => 'Não é possível excluir um bloco com andares vinculados'], 422);
            }

            $bloco->delete();

            return response()->json(['message' => 'Bloco excluído com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir bloco: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir bloco', 'error' => $e->getMessage()], 500);
        }
    }
    public function porPredio($predioId)
{
    $blocos = \App\Models\Bloco::where('predio_id', $predioId)
                ->select('id', 'nome')
                ->orderBy('nome')
                ->get();
    return response()->json($blocos);
}

    /**
     * Exportar relatório de blocos em PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            $query = Bloco::with([
                'predio.campus.instituicao',
                'andares',
            ]);

            // Filtros
            if ($request->filled('instituicao_id')) {
                $query->whereHas('predio.campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->filled('campus_id')) {
                $query->whereHas('predio.campus', function($q) use ($request) {
                    $q->where('id', $request->campus_id);
                });
            }

            if ($request->filled('predio_id')) {
                $query->where('predio_id', $request->predio_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
                });
            }

            $blocos = $query->orderBy('nome')->get();

            [$identidadeVisual, $instituicao] = $this->buscarIdentidadeVisual(
                $request->instituicao_id,
                $blocos->first()
            );

            $logoBase64 = $this->converterLogoBase64($identidadeVisual);

            $data = [
                'blocos' => $blocos,
                'instituicao' => $instituicao,
                'identidadeVisual' => $identidadeVisual,
                'logoBase64' => $logoBase64,
                'dataGeracao' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
                'filtros' => [
                    'instituicao' => $instituicao?->nome_fantasia ?? $instituicao?->razao_social,
                    'campus' => $request->campus_id ? \App\Models\Campus::find($request->campus_id)?->nome : null,
                    'predio' => $request->predio_id ? \App\Models\Predio::find($request->predio_id)?->nome : null,
                ]
            ];

            $pdf = Pdf::loadView('relatorios.blocos', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('relatorio-blocos-' . now()->format('Y-m-d-His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório PDF de blocos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar relatório',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}
