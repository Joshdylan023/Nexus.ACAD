<?php

namespace App\Helpers;

class DocumentHelper
{
    /**
     * Formata CPF
     */
    public static function formatCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }

    /**
     * Formata CNPJ
     */
    public static function formatCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return $cnpj;
        }

        return substr($cnpj, 0, 2) . '.' . 
               substr($cnpj, 2, 3) . '.' . 
               substr($cnpj, 5, 3) . '/' . 
               substr($cnpj, 8, 4) . '-' . 
               substr($cnpj, 12, 2);
    }

    /**
     * Remove formatação de CPF/CNPJ
     */
    public static function onlyNumbers($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}
