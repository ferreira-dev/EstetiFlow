<?php

use App\Support\Telefone;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telefones:normalizar', function () {
    $atualizados = 0;

    // Idempotente: grava apenas dígitos em perfis.telefone (permite busca por match exato)
    DB::table('perfis')
        ->whereNotNull('telefone')
        ->orderBy('id')
        ->chunkById(500, function ($perfis) use (&$atualizados) {
            foreach ($perfis as $perfil) {
                $normalizado = Telefone::normalizar($perfil->telefone);

                if ($normalizado !== $perfil->telefone) {
                    DB::table('perfis')->where('id', $perfil->id)->update(['telefone' => $normalizado]);
                    $atualizados++;
                }
            }
        });

    $this->info("Telefones normalizados: {$atualizados}");
})->purpose('Normaliza perfis.telefone para conter apenas dígitos');
