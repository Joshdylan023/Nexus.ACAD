<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', $value);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            $fail('O CNPJ deve conter 14 dígitos.');
            return;
        }

        // Verifica se todos os dígitos são iguais (CNPJ inválido)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            $fail('O CNPJ informado é inválido.');
            return;
        }

        // Validação dos dígitos verificadores
        for ($t = 12; $t < 14; $t++) {
            $d = 0;
            $c = 0;
            for ($m = $t - 7; $m >= 2; $m--, $c++) {
                $d += $cnpj[$c] * $m;
            }
            for ($m = 9; $m >= 2; $m--, $c++) {
                $d += $cnpj[$c] * $m;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) {
                $fail('O CNPJ informado é inválido.');
                return;
            }
        }
    }
}
