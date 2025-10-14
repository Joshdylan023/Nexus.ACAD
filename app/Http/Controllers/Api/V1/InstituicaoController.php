<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use App\Http\Requests\StoreInstituicaoRequest;
use App\Http\Requests\UpdateInstituicaoRequest;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstituicaoController extends Controller
{
    public function index()
    {
        return Instituicao::with(['mantenedora', 'reitor', 'creator', 'updater'])->get();
    }

    public function store(StoreInstituicaoRequest $request)
    {
        $data = $request->validated();
        
        // Monta endereco_sede
        $enderecoParts = [
            $data['logradouro'] ?? null,
            $data['numero'] ?? null,
            $data['complemento'] ?? null,
            $data['bairro'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['cep'] ?? null
        ];
        
        $data['endereco_sede'] = implode(', ', array_filter($enderecoParts)) ?: 'A definir';
        
        $instituicao = Instituicao::create($data);
        return response()->json($instituicao->load(['mantenedora', 'reitor', 'creator', 'updater']), 201);
    }

    public function show(Instituicao $instituicao)
    {
        return $instituicao->load(['mantenedora', 'reitor', 'campi', 'creator', 'updater']);
    }

    public function update(UpdateInstituicaoRequest $request, Instituicao $instituicao)
    {
        $data = $request->validated();
        
        // Monta endereco_sede
        $enderecoParts = [
            $data['logradouro'] ?? null,
            $data['numero'] ?? null,
            $data['complemento'] ?? null,
            $data['bairro'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['cep'] ?? null
        ];
        
        $data['endereco_sede'] = implode(', ', array_filter($enderecoParts)) ?: 'A definir';
        
        $instituicao->update($data);
        return response()->json($instituicao->load(['mantenedora', 'reitor', 'creator', 'updater']));
    }

    public function destroy(Instituicao $instituicao)
    {
        $instituicao->delete();
        return response()->json(['message' => 'Instituição excluída com sucesso']);
    }

    /**
     * ⭐ EXPORT EXCEL AVANÇADO
     */
    public function export(Request $request)
    {
        // Aplicar filtros
        $query = Instituicao::with(['mantenedora.grupoEducacional']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'ILIKE', "%{$search}%")
                  ->orWhere('nome_fantasia', 'ILIKE', "%{$search}%")
                  ->orWhere('cnpj', 'ILIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('tipo_organizacao_academica')) {
            $query->where('tipo_organizacao_academica', $request->tipo_organizacao_academica);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $data = $query->get();
        
        // ⭐ BUSCAR IDENTIDADE VISUAL (SIMPLIFICADO - USA BUSCA DIRETA NO BANCO)
        $identidade = null;

        // 1. Tentar pegar do request
        if ($request->filled('instituicao_id')) {
            $instituicao = Instituicao::find($request->instituicao_id);
            $identidadeData = DB::table('identidade_visual')
                ->where('entidade_type', 'App\\Models\\Instituicao')
                ->where('entidade_id', $instituicao->id)
                ->first();
            
            if ($identidadeData) {
                $identidade = $identidadeData;
            }
        }

        // 2. Se não encontrou, buscar do primeiro grupo educacional
        if (!$identidade && $data->isNotEmpty()) {
            $primeiraInstituicao = $data->first();
            $primeiraInstituicao->load('mantenedora.grupoEducacional');
            
            $grupoId = $primeiraInstituicao->mantenedora?->grupoEducacional?->id;
            
            if ($grupoId) {
                $identidadeData = DB::table('identidade_visual')
                    ->where('entidade_type', 'App\\Models\\GrupoEducacional')
                    ->where('entidade_id', $grupoId)
                    ->first();
                
                if ($identidadeData) {
                    $identidade = $identidadeData;
                }
            }
        }

        // 3. Se ainda não encontrou, tentar da primeira instituição
        if (!$identidade && $data->isNotEmpty()) {
            foreach ($data as $item) {
                $identidadeData = DB::table('identidade_visual')
                    ->where('entidade_type', 'App\\Models\\Instituicao')
                    ->where('entidade_id', $item->id)
                    ->first();
                
                if ($identidadeData) {
                    $identidade = $identidadeData;
                    break;
                }
            }
        }

        // 4. Se ainda não encontrou, tentar da primeira mantenedora
        if (!$identidade && $data->isNotEmpty()) {
            foreach ($data as $item) {
                $mantenedoraId = $item->mantenedora?->id;
                
                if ($mantenedoraId) {
                    $identidadeData = DB::table('identidade_visual')
                        ->where('entidade_type', 'App\\Models\\Mantenedora')
                        ->where('entidade_id', $mantenedoraId)
                        ->first();
                    
                    if ($identidadeData) {
                        $identidade = $identidadeData;
                        break;
                    }
                }
            }
        }

        // 5. Usar identidade padrão se não encontrar nenhuma
        if (!$identidade) {
            $identidade = (object) [
                'logo_principal' => null,
                'cor_primaria' => '#667EEA',
            ];
        }
        
        $metadata = [
            'titulo' => 'Relatório de Instituições de Ensino Superior',
            'usuario' => auth()->user()->name,
            'total' => $data->count(),
        ];
        
        // Criar Excel
        $excel = new ExcelExportService($identidade, $metadata);
        
        // ⭐ ABA 1: DADOS
        $headers = [
            'ID',
            'Razão Social',
            'Nome Fantasia',
            'Sigla',
            'CNPJ',
            'Tipo',
            'Cidade',
            'Estado',
            'Mantenedora',
            'Grupo Educacional',
            'Status',
            'Criado em',
        ];
        
        $rows = $data->map(function ($item) {
            return [
                $item->id,
                $item->razao_social,
                $item->nome_fantasia ?? '-',
                $item->sigla ?? '-',
                $this->formatCnpj($item->cnpj),
                $item->tipo_organizacao_academica ?? '-',
                $item->cidade ?? '-',
                $item->estado ?? '-',
                $item->mantenedora->razao_social ?? '-',
                $item->mantenedora->grupoEducacional->nome ?? '-',
                $item->status ? 'Ativo' : 'Inativo',
                $item->created_at?->format('d/m/Y H:i'),
            ];
        })->toArray();
        
        $excel->createDataSheet('Instituições', $headers, $rows);
        
        // ⭐ ABA 2: ESTATÍSTICAS
        $stats = [
            ['Total de Instituições', $data->count()],
            ['Ativas', $data->where('status', true)->count()],
            ['Inativas', $data->where('status', false)->count()],
            ['', ''],
            ['Por Estado', ''],
        ];
        
        // Agrupar por estado
        $porEstado = $data->groupBy('estado')->map(function ($items, $estado) {
            return [$estado ?: 'Não informado', $items->count()];
        })->values()->toArray();
        
        $stats = array_merge($stats, $porEstado);
        
        $stats[] = ['', ''];
        $stats[] = ['Por Tipo', ''];
        
        // Agrupar por tipo
        $porTipo = $data->groupBy('tipo_organizacao_academica')->map(function ($items, $tipo) {
            return [$tipo ?: 'Não informado', $items->count()];
        })->values()->toArray();
        
        $stats = array_merge($stats, $porTipo);
        
        $excel->createStatsSheet('Estatísticas', $stats);
        
        // Download
        $filename = 'instituicoes_' . now()->format('Y-m-d_His') . '.xlsx';
        return $excel->download($filename);
    }

    /**
     * Formatar CNPJ
     */
    private function formatCnpj($cnpj)
    {
        if (!$cnpj) return '-';
        $cleaned = preg_replace('/\D/', '', $cnpj);
        if (strlen($cleaned) !== 14) return $cnpj;
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cleaned);
    }
}
