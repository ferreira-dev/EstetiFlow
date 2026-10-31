<?php

namespace App\Support;

class Telefone
{
    /**
     * Mantém apenas os dígitos do telefone (ex.: "(11) 99999-0000" → "11999990000").
     * Retorna null quando não sobra nenhum dígito.
     */
    public static function normalizar(?string $telefone): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefone);

        return $digitos === '' ? null : $digitos;
    }

    /**
     * Mascara o telefone para exibição fora da carteira do profissional
     * (ex.: "11999990000" → "(11) *****-0000").
     */
    public static function mascarar(?string $telefone): ?string
    {
        $digitos = self::normalizar($telefone);

        if ($digitos === null || strlen($digitos) < 6) {
            return null;
        }

        $ddd    = substr($digitos, 0, 2);
        $final  = substr($digitos, -4);
        $oculto = str_repeat('*', strlen($digitos) - 6);

        return "({$ddd}) {$oculto}-{$final}";
    }
}
