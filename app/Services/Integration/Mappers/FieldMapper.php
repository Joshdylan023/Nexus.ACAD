<?php

namespace App\Services\Integration\Mappers;

use Carbon\Carbon;

class FieldMapper
{
    /**
     * Mapear dados de colaborador do sistema externo para o Nexus ACAD
     */
    public static function mapEmployee(array $externalData, array $mappingRules = []): array
    {
        $mapped = [];

        foreach ($mappingRules as $rule) {
            $sourceField = $rule['source_field'];
            $targetField = $rule['target_field'];
            $transform = $rule['transform_function'] ?? null;
            $defaultValue = $rule['default_value'] ?? null;

            // Obter valor do campo de origem
            $value = data_get($externalData, $sourceField);

            // Se não tem valor, usar default
            if ($value === null) {
                $value = $defaultValue;
            }

            // Aplicar transformação se necessário
            if ($value !== null && $transform) {
                $value = self::applyTransform($value, $transform);
            }

            // Setar no array mapeado
            data_set($mapped, $targetField, $value);
        }

        return $mapped;
    }

    /**
     * Aplicar função de transformação
     */
    protected static function applyTransform($value, string $function)
    {
        return match($function) {
            // Strings
            'uppercase' => strtoupper($value),
            'lowercase' => strtolower($value),
            'trim' => trim($value),
            'capitalize' => ucwords(strtolower($value)),
            'slug' => \Str::slug($value),

            // Datas
            'date_br_to_iso' => self::dateBrToIso($value),
            'date_iso_to_br' => self::dateIsoToBr($value),
            'datetime_to_date' => self::datetimeToDate($value),

            // Documentos
            'cpf_format' => self::formatCPF($value),
            'cpf_remove_format' => self::removeCPFFormat($value),
            'cnpj_format' => self::formatCNPJ($value),
            'cnpj_remove_format' => self::removeCNPJFormat($value),

            // Números
            'money_to_cents' => self::moneyToCents($value),
            'cents_to_money' => self::centsToMoney($value),
            'string_to_int' => (int) $value,
            'string_to_float' => (float) $value,

            // Booleanos
            'bool_to_string' => $value ? 'Sim' : 'Não',
            'string_to_bool' => in_array(strtolower($value), ['true', '1', 'sim', 's', 'ativo', 'a']),
            'status_to_bool' => self::statusToBool($value),

            // Telefone
            'phone_format' => self::formatPhone($value),
            'phone_remove_format' => self::removePhoneFormat($value),

            // Default
            default => $value
        };
    }

    // ==========================================
    // TRANSFORMAÇÕES DE DATA
    // ==========================================

    protected static function dateBrToIso($value): ?string
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected static function dateIsoToBr($value): ?string
    {
        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected static function datetimeToDate($value): ?string
    {
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    // ==========================================
    // TRANSFORMAÇÕES DE DOCUMENTO
    // ==========================================

    protected static function formatCPF($value): string
    {
        $cpf = preg_replace('/\D/', '', $value);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    protected static function removeCPFFormat($value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    protected static function formatCNPJ($value): string
    {
        $cnpj = preg_replace('/\D/', '', $value);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    protected static function removeCNPJFormat($value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    // ==========================================
    // TRANSFORMAÇÕES DE TELEFONE
    // ==========================================

    protected static function formatPhone($value): string
    {
        $phone = preg_replace('/\D/', '', $value);
        
        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }
        
        return $phone;
    }

    protected static function removePhoneFormat($value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    // ==========================================
    // TRANSFORMAÇÕES DE DINHEIRO
    // ==========================================

    protected static function moneyToCents($value): int
    {
        // Remove formatação e converte para centavos
        $value = str_replace(['R$', '.', ' '], '', $value);
        $value = str_replace(',', '.', $value);
        return (int) (floatval($value) * 100);
    }

    protected static function centsToMoney($value): string
    {
        return number_format($value / 100, 2, ',', '.');
    }

    // ==========================================
    // TRANSFORMAÇÕES DE STATUS
    // ==========================================

    protected static function statusToBool($value): bool
    {
        $active = ['A', 'ATIVO', 'ACTIVE', 'TRUE', '1', 'SIM', 'S'];
        return in_array(strtoupper($value), $active);
    }

    /**
     * Mapear status do sistema externo para o Nexus ACAD
     */
    public static function mapStatus(string $externalStatus): string
    {
        return match(strtoupper($externalStatus)) {
            'A', 'ATIVO', 'ACTIVE' => 'Ativo',
            'I', 'INATIVO', 'INACTIVE' => 'Inativo',
            'F', 'FERIAS', 'VACATION' => 'Afastado',
            'L', 'LICENCA', 'LICENSE' => 'Afastado',
            'D', 'DEMITIDO', 'FIRED', 'TERMINATED' => 'Desligado',
            default => 'Ativo'
        };
    }

    /**
     * Mapear tipo de vínculo
     */
    public static function mapVinculoType(string $externalType): string
    {
        return match(strtoupper($externalType)) {
            'CLT' => 'CLT',
            'PJ', 'PESSOA_JURIDICA' => 'PJ',
            'EST', 'ESTAGIARIO' => 'Estagiário',
            'TMP', 'TEMPORARIO' => 'Temporário',
            'AUT', 'AUTONOMO' => 'Autônomo',
            default => 'CLT'
        };
    }
}
