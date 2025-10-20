<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Andar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\ExportaPdfTrait;
use Barryvdh\DomPDF\Facade\Pdf;

class AndarController extends Controller
{
    use ExportaPdfTrait;

    public function index(Request $request)
    {
        try {
            $query = Andar::with(['bloco.predio.campus.instituicao', 'espacosFisicos', 'createdBy', 'updatedBy']);

            // ⭐ FILTRO POR BLOCO
            if ($request->has('bloco_id')) {
                $query->where('bloco_id', $request->bloco_id);
            }

            // ⭐ NOVO: FILTRO POR PRÉDIO (através do bloco)
            if ($request->filled('predio_id')) {
                $query->whereHas('bloco', function($q) use ($request) {
                    $q->where('predio_id', $request->predio_id);
                });
            }

            // ⭐ NOVO: FILTRO POR CAMPUS (através do bloco e prédio)
            if ($request->filled('campus_id')) {
                $query->whereHas('bloco.predio', function($q) use ($request) {
                    $q->where('campus_id', $request->campus_id);
                });
            }

            // ⭐ NOVO: FILTRO POR INSTITUIÇÃO (através do bloco, prédio e campus)
            if ($request->filled('instituicao_id')) {
                $query->whereHas('bloco.predio.campus', function($q) use ($request) {
                    $q->where('instituicao_id', $request->instituicao_id);
                });
            }

            // FILTRO POR STATUS
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // BUSCA POR NOME/NÚMERO
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('numero', $search);
                });
            }

            $sortField = $request->get('sort_field', 'numero');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $perPage = $request->get('per_page', 15);
            
            if ($request->has('all') && $request->boolean('all')) {
                return response()->json($query->get());
            }

            return response()->json($query->paginate($perPage));

        } catch (\Exception $e) {
            Log::error('Erro ao listar andares: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao listar andares', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $andar = Andar::with([
                'bloco.predio.campus.instituicao',
                'espacosFisicos.responsavel',
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return response()->json($andar);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Andar não encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bloco_id' => 'required|exists:blocos,id',
                'numero' => 'required|integer',
                'nome' => 'nullable|string|max:255',
                'descricao' => 'nullable|string',
                'area_util' => 'nullable|numeric|min:0',
                'acessibilidade' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção',
                'fotos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do número por bloco
            $exists = Andar::where('bloco_id', $request->bloco_id)
                          ->where('numero', $request->numero)
                          ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um andar com este número neste bloco'], 422);
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $andar = Andar::create($data);

            return response()->json([
                'message' => 'Andar criado com sucesso',
                'data' => $andar->load('bloco')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar andar: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar andar', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $andar = Andar::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'bloco_id' => 'required|exists:blocos,id',
                'numero' => 'required|integer',
                'nome' => 'nullable|string|max:255',
                'descricao' => 'nullable|string',
                'area_util' => 'nullable|numeric|min:0',
                'acessibilidade' => 'boolean',
                'status' => 'required|in:Ativo,Inativo,Manutenção',
                'fotos' => 'nullable|array',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            // Verifica unicidade do número por bloco
            $exists = Andar::where('bloco_id', $request->bloco_id)
                          ->where('numero', $request->numero)
                          ->where('id', '!=', $id)
                          ->exists();

            if ($exists) {
                return response()->json(['message' => 'Já existe um andar com este número neste bloco'], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            // Remove o campo 'created_by' para evitar que ele seja atualizado
            unset($data['created_by']);

            $andar->update($data);

            return response()->json([
                'message' => 'Andar atualizado com sucesso',
                'data' => $andar->load('bloco')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar andar: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao atualizar andar', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $andar = Andar::findOrFail($id);

            if ($andar->espacosFisicos()->count() > 0) {
                return response()->json(['message' => 'Não é possível excluir um andar com espaços físicos vinculados'], 422);
            }

            $andar->delete();

            return response()->json(['message' => 'Andar excluído com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir andar: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir andar', 'error' => $e->getMessage()], 500);
        }
    }

    public function porBloco($blocoId)
    {
        $andares = \App\Models\Andar::where('bloco_id', $blocoId)
                    ->select('id', 'nome', 'numero')
                    ->orderBy('numero')
                    ->get()
                    ->map(function($andar) {
                        return [
                            'id' => $andar->id,
                            'nome' => $andar->nome ?: "Andar {$andar->numero}",
                            'numero' => $andar->numero
                        ];
                    });
        
        return response()->json($andares);
    }

    /**
     * Exportar relatório de andares em PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            // ✅ CORRIGIDO: espacosFisicos ao invés de espacos
            $query = Andar::with([
                'bloco.predio.campus.instituicao',
                'espacosFisicos',
            ]);

            // Filtros
            if ($request->filled('instituicao_id')) {
                $query->whereHas('bloco.predio.campus.instituicao', function($q) use ($request) {
                    $q->where('id', $request->instituicao_id);
                });
            }

            if ($request->filled('campus_id')) {
                $query->whereHas('bloco.predio.campus', function($q) use ($request) {
                    $q->where('id', $request->campus_id);
                });
            }

            if ($request->filled('predio_id')) {
                $query->whereHas('bloco.predio', function($q) use ($request) {
                    $q->where('id', $request->predio_id);
                });
            }

            if ($request->filled('bloco_id')) {
                $query->where('bloco_id', $request->bloco_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'ilike', "%{$search}%")
                      ->orWhere('numero', 'ilike', "%{$search}%");
                });
            }

            $andares = $query->orderBy('numero')->get();

            [$identidadeVisual, $instituicao] = $this->buscarIdentidadeVisual(
                $request->instituicao_id,
                $andares->first()
            );

            $logoBase64 = $this->converterLogoBase64($identidadeVisual);

            $data = [
                'andares' => $andares,
                'instituicao' => $instituicao,
                'identidadeVisual' => $identidadeVisual,
                'logoBase64' => $logoBase64,
                'dataGeracao' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
                'filtros' => [
                    'instituicao' => $instituicao?->nome_fantasia ?? $instituicao?->razao_social,
                    'campus' => $request->campus_id ? \App\Models\Campus::find($request->campus_id)?->nome : null,
                    'predio' => $request->predio_id ? \App\Models\Predio::find($request->predio_id)?->nome : null,
                    'bloco' => $request->bloco_id ? \App\Models\Bloco::find($request->bloco_id)?->nome : null,
                ]
            ];

            $pdf = Pdf::loadView('relatorios.andares', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('relatorio-andares-' . now()->format('Y-m-d-His') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório PDF de andares: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar relatório',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}
