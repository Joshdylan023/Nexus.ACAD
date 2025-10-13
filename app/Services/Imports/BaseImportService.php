<?php

namespace App\Services\Imports;

use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

abstract class BaseImportService
{
    protected $importLog;
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    abstract protected function getImportType(): string;
    abstract protected function getValidationRules(): array;
    abstract protected function processRow(array $row): bool;
    abstract protected function getRequiredColumns(): array;

    public function import(array $data, int $userId, ?int $systemEventId = null): ImportLog
    {
        // Cria log de importação
        $this->importLog = ImportLog::create([
            'system_event_id' => $systemEventId,
            'user_id' => $userId,
            'import_type' => $this->getImportType(),
            'file_name' => $data['file_name'] ?? 'upload.csv',
            'status' => 'processing',
            'total_rows' => count($data['rows']),
            'started_at' => Carbon::now()
        ]);

        DB::beginTransaction();

        try {
            // Valida estrutura do arquivo
            if (!$this->validateColumns($data['rows'][0] ?? [])) {
                throw new \Exception('Colunas do arquivo não correspondem ao esperado.');
            }

            // Processa cada linha
            foreach ($data['rows'] as $index => $row) {
                $lineNumber = $index + 2; // +2 porque linha 1 é cabeçalho e índice começa em 0

                try {
                    if ($this->processRow($row)) {
                        $this->successCount++;
                    } else {
                        $this->errorCount++;
                        $this->errors[] = [
                            'line' => $lineNumber,
                            'error' => 'Erro ao processar linha'
                        ];
                    }
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'line' => $lineNumber,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            // Atualiza log
            $this->importLog->update([
                'status' => 'completed',
                'success_count' => $this->successCount,
                'error_count' => $this->errorCount,
                'errors' => $this->errors,
                'completed_at' => Carbon::now()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->importLog->update([
                'status' => 'failed',
                'errors' => [['error' => $e->getMessage()]],
                'completed_at' => Carbon::now()
            ]);

            throw $e;
        }

        return $this->importLog;
    }

    protected function validateColumns(array $row): bool
    {
        $required = $this->getRequiredColumns();
        $columns = array_keys($row);

        foreach ($required as $column) {
            if (!in_array($column, $columns)) {
                return false;
            }
        }

        return true;
    }

    protected function validateRow(array $row): array
    {
        $validator = Validator::make($row, $this->getValidationRules());

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        return $validator->validated();
    }
}
