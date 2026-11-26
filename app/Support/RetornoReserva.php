<?php

namespace App\Support;

/**
 * URL de retorno para a página pública de reserva após login/registro.
 *
 * Só aceita caminhos relativos do tipo /agendar/{slug} (com ?servico={id} opcional),
 * evitando open redirect para domínios externos.
 */
class RetornoReserva
{
    private const PADRAO = '#^/agendar/[a-z0-9]+(?:-[a-z0-9]+)*(?:\?servico=\d+)?$#';

    public static function validar(?string $url): ?string
    {
        if ($url === null || $url === '' || !preg_match(self::PADRAO, $url)) {
            return null;
        }

        return $url;
    }
}
