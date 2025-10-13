<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Models\SystemEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\Imports\GrupoEducacionalImportService;
use App\Services\Imports\MantenedoraImportService;
use App\Services\Imports\InstituicaoImportService;
use App\Services\Imports\CampusImportService;
use App\Services\Imports\SetorImportService;

class ImportController extends Controller
{
    public function index()
    {
        $logs = ImportLog::with(['user', 'systemEvent'])
            ->whereNotNull('status')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($logs);
    }

    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
            'type' => 'required|in:grupos_educacionais,mantenedoras,instituicoes,campi,setores'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            
            // Verifica extensão manualmente
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                return response()->json([
                    'message' => 'Formato de arquivo inválido. Use CSV, XLSX ou XLS.'
                ], 422);
            }

            // Lê o arquivo usando PhpSpreadsheet
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data)) {
                return response()->json([
                    'message' => 'Arquivo vazio ou inválido'
                ], 422);
            }

            // Pega primeiras 10 linhas para preview
            $rows = array_slice($data, 0, 11); // 1 cabeçalho + 10 linhas
            
            if (empty($rows)) {
                return response()->json([
                    'message' => 'Arquivo não contém dados'
                ], 422);
            }

            $headers = array_shift($rows); // Remove cabeçalho
            
            return response()->json([
                'headers' => $headers,
                'preview' => $rows,
                'total_rows' => count($data) - 1
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro no preview de importação:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Erro ao processar arquivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        // Verifica se há evento ativo
        $activeEvent = SystemEvent::getActiveEvent();
        
        if (!$activeEvent || $activeEvent->type !== 'import') {
            return response()->json([
                'message' => 'É necessário ativar um evento de importação antes de realizar importações em massa.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
            'type' => 'required|in:grupos_educacionais,mantenedoras,instituicoes,campi,setores'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $type = $request->input('type');
        
        // Verifica extensão manualmente
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
            return response()->json([
                'message' => 'Formato de arquivo inválido. Use CSV, XLSX ou XLS.'
            ], 422);
        }

        try {
            // Lê o arquivo usando PhpSpreadsheet
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            $headers = array_shift($data);
            
            $rows = array_map(function($row) use ($headers) {
                return array_combine($headers, $row);
            }, $data);

            // Seleciona service baseado no tipo
            $service = $this->getImportService($type);

            $log = $service->import([
                'file_name' => $file->getClientOriginalName(),
                'rows' => $rows
            ], auth()->id(), $activeEvent->id);

            return response()->json([
                'message' => 'Importação concluída',
                'log' => $log
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro na importação:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Erro na importação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $log = ImportLog::with(['user', 'systemEvent'])->findOrFail($id);
        return response()->json($log);
    }

    private function getImportService(string $type)
    {
        return match($type) {
            'grupos_educacionais' => new GrupoEducacionalImportService(),
            'mantenedoras' => new MantenedoraImportService(),
            'instituicoes' => new InstituicaoImportService(),
            'campi' => new CampusImportService(),
            'setores' => new SetorImportService(),
            default => throw new \Exception('Tipo de importação inválido')
        };
    }
}
