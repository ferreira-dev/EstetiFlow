<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Trava de segurança: RefreshDatabase só roda em banco cujo nome termina com "_testing".
     * Evita apagar o banco de desenvolvimento (ex.: config cacheada ignorando o phpunit.xml).
     */
    protected function setUpTraits()
    {
        $conexao = config('database.default');
        $banco   = (string) config("database.connections.{$conexao}.database");

        if (!str_ends_with($banco, '_testing')) {
            throw new \RuntimeException(
                "Testes abortados: o banco '{$banco}' não é de testes. Rode 'php artisan config:clear' e confira o phpunit.xml."
            );
        }

        return parent::setUpTraits();
    }
}
